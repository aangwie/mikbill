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
        $setting = \App\Models\SiteSetting::first();

        return view('system.update', compact('currentVersion', 'setting'));
    }

    public function saveToken(Request $request)
    {
        $request->validate([
            'github_token' => 'required|string|starts_with:github_pat_',
        ]);

        $setting = \App\Models\SiteSetting::first();
        if (!$setting) {
            $setting = \App\Models\SiteSetting::create([]);
        }

        $setting->update(['github_token' => $request->github_token]);

        return back()->with('success', 'Token GitHub berhasil disimpan.');
    }

    private function getVersion()
    {
        try {
            // Check if git folder exists and is valid
            if (!is_dir(base_path('.git')) || !$this->isValidGitRepo()) {
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

        // Get Token from DB first, then Env
        $setting = \App\Models\SiteSetting::first();
        $githubToken = $setting ? $setting->github_token : env('GITHUB_TOKEN');

        $githubRepo = env('GITHUB_REPO', 'aangwie/mikbill'); // default repo
        $branch = env('GITHUB_BRANCH', 'main');

        // If no .git folder or not a valid repo, try to initialize it
        if (!is_dir(base_path('.git')) || !$this->isValidGitRepo()) {
            if (empty($githubToken)) {
                return back()->with([
                    'status' => 'warning',
                    'message' => 'Git tidak tersedia dan Token belum diset.',
                    'log' => "Project tidak memiliki .git folder.\n\nHarap masukkan GitHub Personal Access Token (PAT) pada form di atas untuk melanjutkan."
                ]);
            }

            // Initialize git with token
            try {
                $log[] = ">>> INITIALIZING GIT...";
                // Use runCommand instead of runCommandSafe to throw on failure here
                $log[] = $this->runCommand('git init 2>&1');

                $remoteUrl = "https://{$githubToken}@github.com/{$githubRepo}.git";

                // Remove remote if exists
                $this->runCommandSafe('git remote remove origin 2>&1');

                $log[] = $this->runCommand("git remote add origin {$remoteUrl} 2>&1");
                $log[] = $this->runCommand("git fetch origin {$branch} 2>&1");
                $log[] = $this->runCommand("git checkout -f -B {$branch} origin/{$branch} 2>&1");
                $log[] = $this->runCommand("git reset --hard origin/{$branch} 2>&1");

                $log[] = "Git repository initialized successfully!";
            } catch (\Exception $e) {
                return back()->with([
                    'status' => 'error',
                    'message' => 'Gagal inisialisasi git repository.',
                    'log' => implode("\n", $log) . "\n\n>>> ERROR:\n" . $e->getMessage()
                ]);
            }
        }

        try {
            // Update remote URL with token if available (for authentication)
            if (!empty($githubToken)) {
                $remoteUrl = "https://{$githubToken}@github.com/{$githubRepo}.git";
                $this->runCommandSafe("git remote set-url origin {$remoteUrl}");
            }

            // 1. GIT FETCH
            $log[] = ">>> FETCHING REMOTE DATA...";
            $this->runCommandSafe("git fetch origin {$branch} 2>&1");

            // 2. COMPARE VERSIONS
            $localInfo = $this->runCommandSafe('git log -1 --format="%h - %s (%cd)" --date=short HEAD');
            $remoteInfo = $this->runCommandSafe("git log -1 --format=\"%h - %s (%cd)\" --date=short origin/{$branch}");

            $localHash = trim($this->runCommandSafe('git rev-parse HEAD'));
            $remoteHash = trim($this->runCommandSafe("git rev-parse origin/{$branch}"));

            $log[] = "--------------------------------------------------";
            $log[] = "LOCAL  : " . $localInfo;
            $log[] = "REMOTE : " . $remoteInfo;
            $log[] = "--------------------------------------------------";

            if ($localHash === $remoteHash) {
                return back()->with([
                    'status' => 'info',
                    'message' => 'Sistem Anda sudah menggunakan versi terbaru.',
                    'log' => implode("\n", $log)
                ]);
            }

            $log[] = ">>> UPDATE AVAILABLE. STARTING PROCESS...";

            // 3. RESET TO REMOTE (Force Pull)
            $resetOutput = $this->runCommandSafe("git reset --hard origin/{$branch} 2>&1");
            $log[] = ">>> GIT RESET:\n" . $resetOutput;

            // 4. MIGRATION & OPTIMIZATION
            $log[] = ">>> MIGRATING DATABASE...";
            $log[] = $this->runCommandSafe('php artisan migrate --force 2>&1');

            $log[] = ">>> CLEARING CACHE...";
            $log[] = $this->runCommandSafe('php artisan optimize:clear 2>&1');

            // 5. COMPOSER
            if (file_exists(base_path('composer.json'))) {
                $log[] = ">>> UPDATING DEPENDENCIES...";
                $log[] = $this->runCommandSafe('composer install --no-dev --optimize-autoloader 2>&1');
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
            'log' => implode("\n", $log)
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
     * Run database migrations manually
     */
    public function migrate()
    {
        $log = [];
        try {
            $log[] = ">>> RUNNING MIGRATIONS...";
            $migrateOutput = $this->runCommandSafe('php artisan migrate --force 2>&1');
            $log[] = $migrateOutput;

            return back()->with([
                'status' => 'success',
                'message' => 'Database berhasil di-update!',
                'log' => implode("\n", $log)
            ]);
        } catch (\Exception $e) {
            return back()->with([
                'status' => 'error',
                'message' => 'Gagal menjalankan migrasi.',
                'log' => $e->getMessage()
            ]);
        }
    }

    /**
     * Backup database and download as .sql
     */
    public function backup()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');

        $filename = "backup-" . $database . "-" . date('Y-m-d-H-i-s') . ".sql";
        $path = storage_path('app/' . $filename);

        // Path to mysqldump on XAMPP Windows
        $mysqldump = 'C:\xampp\mysql\bin\mysqldump.exe';
        if (!file_exists($mysqldump)) {
            $mysqldump = 'mysqldump'; // Try fallback to PATH
        }

        // Use localhost instead of 127.0.0.1 for better Windows compatibility in some cases
        $host = ($host == '127.0.0.1') ? 'localhost' : $host;
        $passwordFlag = !empty($password) ? "--password=\"{$password}\"" : "";

        $command = "\"{$mysqldump}\" --user={$username} {$passwordFlag} --host={$host} --protocol=tcp {$database} > \"{$path}\"";

        try {
            $output = $this->runCommandSafe($command);

            if (file_exists($path) && filesize($path) > 0) {
                return response()->download($path)->deleteFileAfterSend(true);
            } else {
                $errorLog = ">>> COMMAND:\n{$command}\n\n>>> OUTPUT:\n{$output}";
                return back()->with([
                    'status' => 'error',
                    'message' => 'Gagal membuat file backup. Periksa koneksi database atau path mysqldump.',
                    'log' => $errorLog
                ]);
            }
        } catch (\Exception $e) {
            return back()->with([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat backup.',
                'log' => $e->getMessage()
            ]);
        }
    }

    /**
     * Restore database from uploaded .sql file
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt',
        ]);

        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');

        $file = $request->file('backup_file');
        $path = $file->storeAs('temp', 'restore-' . time() . '.sql');
        $fullPath = storage_path('app/' . $path);

        // Path to mysql on XAMPP Windows
        $mysql = 'C:\xampp\mysql\bin\mysql.exe';
        if (!file_exists($mysql)) {
            $mysql = 'mysql'; // Try fallback to PATH
        }

        $command = "\"{$mysql}\" --user={$username} --password=\"{$password}\" --host={$host} {$database} < \"{$fullPath}\"";

        try {
            $output = $this->runCommandSafe($command);
            @unlink($fullPath);

            return back()->with([
                'status' => 'success',
                'message' => 'Database berhasil direstore!',
                'log' => ">>> DATABASE RESTORE:\n" . ($output ?: "Berhasil diimport.")
            ]);
        } catch (\Exception $e) {
            @unlink($fullPath);
            return back()->with([
                'status' => 'error',
                'message' => 'Gagal melakukan restore database.',
                'log' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create storage symlink for shared hosting
     * Links: DOCUMENT_ROOT/storage -> laravel_base/storage/app/public
     */
    public function createSymlink()
    {
        $log = [];

        try {
            // Target: Laravel's storage/app/public
            $target = storage_path('app/public');

            // Link: DOCUMENT_ROOT/storage (public_html/storage on shared hosting)
            $link = (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']))
                ? $_SERVER['DOCUMENT_ROOT'] . '/storage'
                : public_path('storage');

            $log[] = ">>> STORAGE SYMLINK";
            $log[] = "Target : " . $target;
            $log[] = "Link   : " . $link;
            $log[] = "--------------------------------------------------";

            // Check if target directory exists
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
                $log[] = "Created target directory: " . $target;
            }

            // Remove existing symlink or directory
            if (is_link($link)) {
                unlink($link);
                $log[] = "Removed existing symlink.";
            } elseif (is_dir($link)) {
                // If it's a real directory (not symlink), rename it as backup
                rename($link, $link . '_backup_' . date('Ymd_His'));
                $log[] = "Existing directory renamed to backup.";
            }

            // Create symlink
            if (symlink($target, $link)) {
                $log[] = ">>> Symlink created successfully!";

                return back()->with([
                    'status' => 'success',
                    'message' => 'Storage symlink berhasil dibuat!',
                    'log' => implode("\n", $log)
                ]);
            } else {
                $log[] = ">>> Failed to create symlink. Trying alternative method...";

                // Alternative: Use Artisan command
                $artisanOutput = $this->runCommandSafe('php artisan storage:link 2>&1');
                $log[] = $artisanOutput;

                return back()->with([
                    'status' => 'success',
                    'message' => 'Storage symlink dibuat via artisan!',
                    'log' => implode("\n", $log)
                ]);
            }
        } catch (\Exception $e) {
            $log[] = ">>> ERROR: " . $e->getMessage();
            return back()->with([
                'status' => 'error',
                'message' => 'Gagal membuat storage symlink.',
                'log' => implode("\n", $log)
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

    /**
     * Check if current directory is a valid git repository
     */
    private function isValidGitRepo()
    {
        $process = Process::fromShellCommandline('git rev-parse --is-inside-work-tree', base_path());
        $process->run();
        return trim($process->getOutput()) === 'true';
    }
}