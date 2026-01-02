<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;
use Carbon\Carbon;

class CleanupExpiredHotspotUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotspot:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired hotspot users based on comment timestamp';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(MikrotikService $mikrotik)
    {
        $this->info('Starting Hotspot User Cleanup...');

        if (!$mikrotik->isConnected()) {
            $this->error('Could not connect to Mikrotik.');
            return Command::FAILURE;
        }

        $users = $mikrotik->getHotspotUsers();
        $now = Carbon::now();
        $count = 0;

        foreach ($users as $user) {
            $comment = $user['comment'] ?? '';
            $uptime = $user['uptime'] ?? '0s';
            $name = $user['name'];

            // Logic 1: Convert REM: to EXP: on first login (uptime > 0)
            if (strpos($comment, 'REM:') === 0 && $uptime !== '0s') {
                $period = str_replace('REM:', '', $comment);
                $expiry = Carbon::now();

                if ($period == '1d')
                    $expiry->addDay();
                elseif ($period == '1w')
                    $expiry->addWeek();
                elseif ($period == '1m')
                    $expiry->addMonth();

                $expiryStr = "EXP:" . $expiry->format('Y-m-d H:i:s');
                $this->info("User $name activated! Setting expiry: $expiryStr");

                // Update comment in Mikrotik
                $this->updateUserComment($mikrotik, $name, $expiryStr);
                continue;
            }

            // Logic 2: Handle EXP: for actual deletion
            if (strpos($comment, 'EXP:') === 0) {
                $dateStr = str_replace('EXP:', '', $comment);
                try {
                    $expiry = Carbon::createFromFormat('Y-m-d H:i:s', $dateStr);

                    if ($now->greaterThan($expiry)) {
                        $this->info("Removing expired user: $name (Expired at: $dateStr)");
                        if ($mikrotik->removeHotspotUser($name)) {
                            $count++;
                        }
                    }
                } catch (\Exception $e) {
                    $this->warn("Invalid expiration format for user $name: " . $comment);
                }
            }
        }

        $this->info("Cleanup finished. Removed $count expired users.");

        return Command::SUCCESS;
    }

    private function updateUserComment($mikrotik, $name, $comment)
    {
        // We need a way to update the comment. 
        // Let's check if MikrotikService has a method for this.
        // If not, we use the client directly or add a helper.
        $client = $mikrotik->getClient(); // We need to expose this or add a method.
        if (!$client)
            return;

        $queryFind = (new \RouterOS\Query('/ip/hotspot/user/print'))->where('name', $name);
        $user = $client->query($queryFind)->read();

        if (!empty($user)) {
            $id = $user[0]['.id'];
            $querySet = (new \RouterOS\Query('/ip/hotspot/user/set'))
                ->equal('.id', $id)
                ->equal('comment', $comment);
            $client->query($querySet)->read();
        }
    }
}
