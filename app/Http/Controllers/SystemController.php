<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SystemController extends Controller
{
    public function index()
    {
        // Ambil info versi terakhir (Hash Commit & Pesan)
        $currentVersion = $this->runCommand('git log -1 --format="%h - %s (%cd)" --date=short');
        
        return view('system.update', compact('currentVersion'));
    }

    public function update(Request $request)
    {
        $log = [];

        try {
            // 1. GIT PULL (Tarik Data dari GitHub)
            // 2>&1 berguna untuk menangkap pesan error juga
            $gitOutput = $this->runCommand('git pull origin main 2>&1');
            $log[] = ">>> GIT PULL:\n" . $gitOutput;

            // Jika ada perubahan (bukan Already up to date), jalankan perintah lain
            if (!str_contains($gitOutput, 'Already up to date')) {
                
                // 2. MIGRATE DATABASE (Jika ada perubahan struktur tabel)
                // --force diperlukan karena di production
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
            'log' => implode("\n\n", $log) // Kirim log ke view untuk ditampilkan
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
}