<?php

namespace App\Http\Controllers;

use App\Models\WhatsappSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    protected $waService;

    public function __construct(WhatsappService $waService)
    {
        $this->waService = $waService;
    }

    public function index()
    {
        $setting = WhatsappSetting::first();

        // Ambil pelanggan yang punya Nomor HP saja
        $customers = Customer::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name', 'asc')
            ->get();

        return view('whatsapp.index', compact('setting', 'customers'));
    }

    // Simpan Konfigurasi
    public function update(Request $request)
    {
        $data = $request->validate([
            'target_url' => 'required|url',
            'api_key' => 'required|string',
            'sender_number' => 'nullable|string',
        ]);

        $setting = WhatsappSetting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            WhatsappSetting::create($data);
        }

        return back()->with('success', 'Pengaturan WhatsApp disimpan.');
    }

    // Test Kirim Pesan (Satu Nomor)
    public function sendTest(Request $request)
    {
        $request->validate(['target' => 'required', 'message' => 'required']);

        $result = $this->waService->send($request->target, $request->message);

        if ($result['status']) {
            return back()->with('success', 'Pesan terkirim! Response: ' . $result['response']);
        } else {
            return back()->with('error', 'Gagal: ' . $result['message']);
        }
    }

    // Broadcast (Masal)
    public function broadcast(Request $request)
    {
        $type = $request->type; // 'unpaid', 'paid', 'all'
        $messageTemplate = $request->message;

        $targets = [];

        if ($type == 'unpaid') {
            // Ambil user yang punya invoice unpaid
            $invoices = Invoice::with('customer')->where('status', 'unpaid')->get();
            foreach ($invoices as $inv) {
                $targets[] = [
                    'phone' => $inv->customer->phone,
                    'name' => $inv->customer->name,
                    'bill' => $inv->customer->monthly_price
                ];
            }
        } elseif ($type == 'all') {
            $customers = Customer::whereNotNull('phone')->get();
            foreach ($customers as $c) {
                $targets[] = ['phone' => $c->phone, 'name' => $c->name, 'bill' => 0];
            }
        }

        $count = 0;
        foreach ($targets as $target) {
            if (!empty($target['phone'])) {
                // Replace variabel dinamis {name} dan {bill}
                $msg = str_replace('{name}', $target['name'], $messageTemplate);
                $msg = str_replace('{tagihan}', number_format($target['bill']), $msg);

                $this->waService->send($target['phone'], $msg);
                $count++;
            }
        }

        return back()->with('success', "Broadcast sedang diproses ke $count nomor.");
    }

    // Kirim ke Satu Pelanggan (Dipilih dari Dropdown)
    // Kirim ke BANYAK Pelanggan (Multi Select)
    public function sendToCustomer(Request $request)
    {
        // Validasi: customer_ids sekarang harus berupa ARRAY
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id', // Pastikan setiap ID valid
            'message' => 'required',
        ]);

        $successCount = 0;
        $failCount = 0;

        // Loop setiap customer yang dipilih
        foreach ($request->customer_ids as $id) {
            $customer = Customer::find($id);

            if ($customer && !empty($customer->phone)) {
                // Replace variable {name} & {tagihan} unik per customer
                $msg = str_replace('{name}', $customer->name, $request->message);
                $msg = str_replace('{tagihan}', number_format($customer->monthly_price, 0, ',', '.'), $msg);

                // Kirim Pesan
                $result = $this->waService->send($customer->phone, $msg);

                if ($result['status']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
        }

        // Berikan feedback hasil pengiriman
        if ($successCount > 0) {
            return back()->with('success', "Pesan berhasil dikirim ke $successCount pelanggan." . ($failCount > 0 ? " ($failCount gagal)" : ""));
        } else {
            return back()->with('error', "Gagal mengirim pesan. Periksa nomor tujuan.");
        }
    }

    // HALAMAN UTAMA BROADCAST
    public function broadcastIndex()
    {
        // Ambil ID, Nama, dan No HP semua pelanggan yang punya nomor HP
        $targets = Customer::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->select('id', 'name', 'phone')
            ->get();

        return view('whatsapp.broadcast', compact('targets'));
    }

    // PROSES KIRIM PER ITEM (Dipanggil AJAX)
    public function broadcastProcess(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'message' => 'required',
        ]);

        $customer = Customer::find($request->id);

        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan']);
        }

        // Replace variable dinamis
        $msg = str_replace('{name}', $customer->name, $request->message);
        $msg = str_replace('{tagihan}', number_format($customer->monthly_price, 0, ',', '.'), $msg);

        // Kirim WA
        try {
            $result = $this->waService->send($customer->phone, $msg);

            if ($result['status']) {
                return response()->json([
                    'status' => true,
                    'target' => $customer->name,
                    'phone' => $customer->phone
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'target' => $customer->name,
                    'message' => 'Gagal koneksi WA'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'target' => $customer->name,
                'message' => $e->getMessage()
            ]);
        }
    }

    // API: Ambil Daftar Target untuk Broadcast (Dipanggil AJAX)
    public function getBroadcastTargets(Request $request)
    {
        $type = $request->type; // 'unpaid' atau 'all'

        if ($type == 'unpaid') {
            // Cari pelanggan yang punya invoice status != paid
            // Asumsi: Relasi customer -> invoices sudah ada
            // Atau query manual sederhana:
            $targets = Customer::whereHas('invoices', function ($q) {
                $q->where('status', '!=', 'paid');
            })
                ->whereNotNull('phone')
                ->get(['id', 'name', 'phone', 'monthly_price']); // Ambil monthly_price untuk variabel {tagihan}

        } else {
            // Semua Pelanggan
            $targets = Customer::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get(['id', 'name', 'phone', 'monthly_price']);
        }

        return response()->json($targets);
    }
    // --- GATEWAY PROXY ---
    private function getGatewayUrl($path = '')
    {
        $baseUrl = rtrim(env('WHATSAPP_GATEWAY_URL', 'http://localhost:3000'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    public function getStatus()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($this->getGatewayUrl('status'), ['http_errors' => false, 'timeout' => 10]);
            return $response->getBody();
        } catch (\Exception $e) {
            return response()->json(['status' => 'ERROR', 'message' => 'Gateway Backend is OFF']);
        }
    }

    public function getQr()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($this->getGatewayUrl('qr'), ['http_errors' => false, 'timeout' => 10]);
            return $response->getBody();
        } catch (\Exception $e) {
            return response()->json(['status' => 'ERROR']);
        }
    }

    public function logout()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $client->post($this->getGatewayUrl('logout'), ['http_errors' => false, 'timeout' => 10]);
            return back()->with('success', 'WhatsApp Disconnected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to disconnect gateway.');
        }
    }
    public function startGateway()
    {
        $path = base_path('whatsapp-gateway');
        $script = $path . DIRECTORY_SEPARATOR . 'index.js';

        if (!file_exists($script)) {
            return back()->with('error', 'File index.js not found in whatsapp-gateway folder.');
        }

        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: Use batch file helper for reliable start
                $batchFile = $path . DIRECTORY_SEPARATOR . 'start_gateway.bat';
                pclose(popen("start /B \"\" \"$batchFile\"", "r"));
            } else {
                // Linux / Hosting
                // Menggunakan nohup untuk background process
                // Pastikan fungsi exec() aktif di hosting
                exec("cd \"$path\" && nohup node index.js > /dev/null 2>&1 &");
            }

            // Tunggu sebentar agar server nyala
            sleep(3);

            return back()->with('success', 'Perintah START dikirim. Silakan Klik Refresh Status.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan server: ' . $e->getMessage());
        }
    }

    public function stopGateway()
    {
        // 1. Try graceful logout first
        try {
            $client = new \GuzzleHttp\Client();
            $client->post($this->getGatewayUrl('logout'), ['http_errors' => false, 'timeout' => 2]);
        } catch (\Exception $e) {
            // Retrieve error
        }

        // 2. Clear Database Session (Delete All)
        try {
            \Illuminate\Support\Facades\DB::table('whatsapp_sessions')->delete();
        } catch (\Exception $e) {
            // DB Error
        }

        // 3. Stop Node.js Process
        $path = base_path('whatsapp-gateway');
        $pidFile = $path . DIRECTORY_SEPARATOR . 'gateway.pid';
        $killed = false;

        // Try graceful shutdown via HTTP
        try {
            $client = new \GuzzleHttp\Client();
            $client->post($this->getGatewayUrl('shutdown'), ['http_errors' => false, 'timeout' => 2]);
        } catch (\Exception $e) {
            // Ignore
        }

        // Kill by PID if exists
        if (file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            if ($pid && is_numeric($pid)) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    exec("taskkill /F /PID $pid", $out, $ret);
                } else {
                    exec("kill -9 $pid");
                }
                $killed = true;
            }
            @unlink($pidFile);
        }

        // Fallback Force Kill
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("taskkill /F /IM node.exe");
        }

        sleep(2); // Wait for cleanup

        return back()->with('success', 'Service Node.js Stopped, Session Cleared.');
    }

    public function regenerateApiKey()
    {
        $user = auth()->user();
        $user->api_token = \Illuminate\Support\Str::random(60);
        $user->save();

        return back()->with('success', 'API Key Generated successfully.');
    }

    /**
     * Setup / Documentation Page
     */
    public function setup()
    {
        return view('whatsapp.setup');
    }

    /**
     * Check if Node.js is installed on server
     * Note: On CloudLinux/cPanel, PHP can't detect Node.js via exec()
     * So we also check if the gateway is responding
     */
    public function checkNodejs()
    {
        $nodePath = null;
        $nodeVersion = null;
        $npmVersion = null;
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $gatewayRunning = false;
        $isCpanel = false;

        // Check if running on cPanel (CloudLinux)
        if (!$isWindows && (file_exists('/usr/local/cpanel/cpanel') || is_dir('/home') && file_exists('/etc/cpanel'))) {
            $isCpanel = true;
        }

        // Try to find node via exec (works on localhost, not on cPanel)
        if ($isWindows) {
            exec('where node 2>nul', $nodeOutput, $nodeReturn);
        } else {
            exec('which node 2>/dev/null', $nodeOutput, $nodeReturn);
        }

        if ($nodeReturn === 0 && !empty($nodeOutput)) {
            $nodePath = trim($nodeOutput[0]);

            exec('node -v 2>&1', $versionOutput);
            $nodeVersion = isset($versionOutput[0]) ? trim($versionOutput[0]) : null;

            exec('npm -v 2>&1', $npmOutput);
            $npmVersion = isset($npmOutput[0]) ? trim($npmOutput[0]) : null;
        }

        // Check if gateway is actually responding (works for cPanel)
        $checkError = null;
        try {
            $client = new \GuzzleHttp\Client(['verify' => false]); // Disable SSL verify for internal check
            $response = $client->get($this->getGatewayUrl('status'), [
                'http_errors' => false,
                'timeout' => 5
            ]);
            $body = json_decode($response->getBody(), true);
            if ($response->getStatusCode() == 200 && isset($body['status'])) {
                $gatewayRunning = true;
            } else {
                $checkError = "Status code: " . $response->getStatusCode() . ". Response: " . substr($response->getBody(), 0, 100);
            }
        } catch (\Exception $e) {
            $gatewayRunning = false;
            $checkError = $e->getMessage();
        }

        // Check if node_modules exists (look in root or gateway folder)
        $nodeModulesExists = is_dir(base_path('node_modules')) || is_dir(base_path('whatsapp-gateway/node_modules'));

        // Determine if Node.js is available
        // On cPanel: we can't detect via exec, but if gateway runs then Node.js works
        $nodeAvailable = ($nodePath !== null) || $gatewayRunning;

        return response()->json([
            'installed' => $nodeAvailable,
            'node_path' => $nodePath,
            'node_version' => $nodeVersion ?: ($gatewayRunning ? 'Detected via Gateway' : null),
            'npm_version' => $npmVersion,
            'os' => $isWindows ? 'Windows' : 'Linux/Unix',
            'is_cpanel' => $isCpanel,
            'gateway_running' => $gatewayRunning,
            'gateway_url' => env('WHATSAPP_GATEWAY_URL', 'http://localhost:3000'),
            'gateway_path' => base_path(),
            'dependencies_installed' => $nodeModulesExists,
            'check_error' => $checkError,
            'message' => $isCpanel && !$nodePath ? 'cPanel detected. PHP cannot detect Node.js directly. Please ensure WHATSAPP_GATEWAY_URL in .env matches your cPanel Application URL (e.g., https://billnesia.com).' : null,
        ]);
    }

    /**
     * Install gateway dependencies
     */
    public function installDependencies()
    {
        $rootPath = base_path(); // npm install from project root
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // Run npm install from project root (where package.json is)
        if ($isWindows) {
            $command = "cd /d \"$rootPath\" && npm install 2>&1";
        } else {
            $command = "cd \"$rootPath\" && npm install 2>&1";
        }

        exec($command, $output, $return);

        if ($return === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Dependencies installed successfully!',
                'output' => implode("\n", $output)
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to install dependencies',
                'output' => implode("\n", $output)
            ], 500);
        }
    }
}
