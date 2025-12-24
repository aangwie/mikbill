<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SystemController extends Controller
{
    public function index()
    {
        // Check if git is available
        $currentVersion = $this->getVersion();

        return view('system.update', compact('currentVersion'));
    }

    private function getVersion()
    {
        try {
            // Check if .git folder exists
            if (!is_dir(base_path('.git'))) {
                return 'Manual Upload (No Git)';
            }
            return $this->runCommand('git log -1 --format="%h - %s (%cd)" --date=short');
        } catch (\Exception $e) {
            return 'Version Unknown';
        }
    }

    public function update(Request $request)
    {
        $log = [];

        // Check if git is available first
        if (!is_dir(base_path('.git'))) {
            return back()->with([
                'status' => 'warning',
                'message' => 'Git tidak tersedia. Project ini di-upload manual. Silakan upload ulang file secara manual untuk update.',
                'log' => 'Project tidak menggunakan Git repository. Untuk update:\n1. Download versi terbaru dari GitHub\n2. Upload/replace file via File Manager atau FTP\n3. Jalankan: php artisan migrate --force\n4. Jalankan: php artisan optimize:clear'
            ]);
        }

        try {
            // 1. GIT PULL (Tarik Data dari GitHub)
            $gitOutput = $this->runCommand('git pull origin main 2>&1');
            $log[] = ">>> GIT PULL:\n" . $gitOutput;

            // Jika ada perubahan (bukan Already up to date), jalankan perintah lain
            if (!str_contains($gitOutput, 'Already up to date')) {

                // 2. MIGRATE DATABASE
                $migrateOutput = $this->runCommand('php artisan migrate --force 2>&1');
                $log[] = ">>> MIGRATION:\n" . $migrateOutput;

                // 3. OPTIMIZE (Bersihkan Cache)
                $optimizeOutput = $this->runCommand('php artisan optimize:clear 2>&1');
                $log[] = ">>> OPTIMIZE:\n" . $optimizeOutput;

                $status = 'success';
                $message = 'Sistem berhasil diperbarui ke versi terbaru!';
            } else {
                $status = 'info';
                $message = 'Sistem Anda sudah menggunakan versi terbaru.';
            }

        } catch (\Exception $e) {
            $status = 'error';
            $message = 'Terjadi kesalahan saat update.';
            $log[] = ">>> ERROR:\n" . $e->getMessage();
        }

        return back()->with([
            'status' => $status,
            'message' => $message,
            'log' => implode("\n\n", $log)
        ]);
    }

    // Fungsi Helper untuk menjalankan perintah Shell
    private function runCommand($command)
    {
        // Jalankan perintah di root folder proyek
        $process = Process::fromShellCommandline($command, base_path());
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }

    /**
     * Clear all Laravel caches
     */
    public function clearCache()
    {
        $log = [];

        try {
            // Config clear
            $configOutput = $this->runCommandSafe('php artisan config:clear 2>&1');
            $log[] = ">>> CONFIG CLEAR:\n" . $configOutput;

            // Cache clear
            $cacheOutput = $this->runCommandSafe('php artisan cache:clear 2>&1');
            $log[] = ">>> CACHE CLEAR:\n" . $cacheOutput;

            // View clear
            $viewOutput = $this->runCommandSafe('php artisan view:clear 2>&1');
            $log[] = ">>> VIEW CLEAR:\n" . $viewOutput;

            // Route clear
            $routeOutput = $this->runCommandSafe('php artisan route:clear 2>&1');
            $log[] = ">>> ROUTE CLEAR:\n" . $routeOutput;

            return back()->with([
                'status' => 'success',
                'message' => 'Cache berhasil dibersihkan!',
                'log' => implode("\n\n", $log)
            ]);
        } catch (\Exception $e) {
            return back()->with([
                'status' => 'error',
                'message' => 'Gagal membersihkan cache.',
                'log' => $e->getMessage()
            ]);
        }
    }

    /**
     * Run command without throwing exception
     */
    private function runCommandSafe($command)
    {
        $process = Process::fromShellCommandline($command, base_path());
        $process->run();
        return trim($process->getOutput() ?: $process->getErrorOutput());
    }
}