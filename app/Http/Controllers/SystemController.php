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
        $githubToken = env('GITHUB_TOKEN');
        $githubRepo = env('GITHUB_REPO', 'aangwie/mikbill'); // default repo
        $branch = env('GITHUB_BRANCH', 'main');

        // If no .git folder, try to initialize it
        if (!is_dir(base_path('.git'))) {
            if (empty($githubToken)) {
                return back()->with([
                    'status' => 'warning',
                    'message' => 'Git tidak tersedia dan GITHUB_TOKEN belum diset.',
                    'log' => "Project tidak memiliki .git folder.\n\nUntuk mengaktifkan auto-update, tambahkan ke file .env:\n\nGITHUB_TOKEN=your_personal_access_token\nGITHUB_REPO=username/repository\nGITHUB_BRANCH=main"
                ]);
            }

            // Initialize git with token
            try {
                $log[] = ">>> INITIALIZING GIT...";
                $this->runCommandSafe('git init');

                $remoteUrl = "https://{$githubToken}@github.com/{$githubRepo}.git";
                $this->runCommandSafe("git remote add origin {$remoteUrl}");
                $this->runCommandSafe('git fetch origin');
                $this->runCommandSafe("git checkout -f origin/{$branch}");
                $this->runCommandSafe("git branch -M {$branch}");
                $this->runCommandSafe("git reset --hard origin/{$branch}");

                $log[] = "Git repository initialized successfully!";
            } catch (\Exception $e) {
                return back()->with([
                    'status' => 'error',
                    'message' => 'Gagal inisialisasi git repository.',
                    'log' => $e->getMessage()
                ]);
            }
        }

        try {
            // Update remote URL with token if available (for authentication)
            if (!empty($githubToken)) {
                $remoteUrl = "https://{$githubToken}@github.com/{$githubRepo}.git";
                $this->runCommandSafe("git remote set-url origin {$remoteUrl}");
            }

            // 1. GIT FETCH & RESET (more reliable than pull)
            $fetchOutput = $this->runCommandSafe("git fetch origin {$branch} 2>&1");
            $log[] = ">>> GIT FETCH:\n" . $fetchOutput;

            // Check if there are changes
            $localHash = trim($this->runCommandSafe('git rev-parse HEAD'));
            $remoteHash = trim($this->runCommandSafe("git rev-parse origin/{$branch}"));

            if ($localHash === $remoteHash) {
                return back()->with([
                    'status' => 'info',
                    'message' => 'Sistem Anda sudah menggunakan versi terbaru.',
                    'log' => implode("\n\n", $log) . "\n\nLocal: {$localHash}\nRemote: {$remoteHash}"
                ]);
            }

            // Reset to remote version
            $resetOutput = $this->runCommandSafe("git reset --hard origin/{$branch} 2>&1");
            $log[] = ">>> GIT RESET:\n" . $resetOutput;

            // 2. MIGRATE DATABASE
            $migrateOutput = $this->runCommandSafe('php artisan migrate --force 2>&1');
            $log[] = ">>> MIGRATION:\n" . $migrateOutput;

            // 3. OPTIMIZE (Bersihkan Cache)
            $optimizeOutput = $this->runCommandSafe('php artisan optimize:clear 2>&1');
            $log[] = ">>> OPTIMIZE:\n" . $optimizeOutput;

            // 4. Composer install (if needed)
            if (file_exists(base_path('composer.json'))) {
                $composerOutput = $this->runCommandSafe('composer install --no-dev --optimize-autoloader 2>&1');
                $log[] = ">>> COMPOSER:\n" . $composerOutput;
            }

            $status = 'success';
            $message = 'Sistem berhasil diperbarui ke versi terbaru!';

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