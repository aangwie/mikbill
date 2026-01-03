<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HotspotController extends Controller
{
    protected $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function monitor()
    {
        $activeUsers = $this->mikrotik->getHotspotActive();
        return view('monitor.hotspot', compact('activeUsers'));
    }

    public function generateForm()
    {
        $isConnected = $this->mikrotik->isConnected();
        $hotspotServers = $isConnected ? $this->mikrotik->getHotspotServers() : [];
        $isHotspotReady = !empty($hotspotServers);

        $profiles = $isConnected ? $this->mikrotik->getHotspotProfiles() : [];
        $users = $isConnected ? $this->mikrotik->getHotspotUsers() : [];

        // Filter users that have our comment format (REM: or EXP:)
        $managedUsers = array_filter($users, function ($user) {
            $comment = $user['comment'] ?? '';
            return strpos($comment, 'REM:') === 0 || strpos($comment, 'EXP:') === 0;
        });

        // Add remaining time calculation
        foreach ($managedUsers as &$user) {
            $user['remaining_time'] = $this->calculateRemainingTime($user);
        }

        return view('hotspot.generate', compact('profiles', 'managedUsers', 'isConnected', 'isHotspotReady'));
    }

    public function generateStore(Request $request)
    {
        $request->validate([
            'prefix' => 'nullable|string|max:10',
            'quantity' => 'required|integer|min:1|max:100',
            'profile' => 'required|string',
            'period' => 'required|in:1d,1w,1m',
        ]);

        $prefix = $request->prefix ?? '';
        $quantity = $request->quantity;
        $profile = $request->profile;
        $period = $request->period;

        // Check Plan Limit
        $user = auth()->user();
        $admin = $user->isAdmin() ? $user : $user->parent;
        $plan = $admin->plan;

        if ($plan && $plan->max_vouchers > 0) {
            $existingUsers = $this->mikrotik->getHotspotUsers();
            $currentCount = count($existingUsers);

            if (($currentCount + $quantity) > $plan->max_vouchers) {
                return redirect()->route('hotspot.generate')->with('error', "Batas pembuatan voucher tercapai. Paket Anda memperbolehkan maksimal {$plan->max_vouchers} voucher (Saat ini: {$currentCount}).");
            }
        }

        // Comment format is REM:1d, REM:1w, REM:1m
        // Expiration will be set on first login by cleanup command
        $comment = "REM:" . $period;

        $generatedCount = 0;

        for ($i = 0; $i < $quantity; $i++) {
            $username = $prefix . $this->generateDigits(6);
            $password = $this->generateDigits(6);

            $data = [
                'name' => $username,
                'password' => $password,
                'profile' => $profile,
                'comment' => $comment,
            ];

            if ($this->mikrotik->addHotspotUser($data)) {
                $generatedCount++;
            }
        }

        return redirect()->route('hotspot.generate')->with([
            'success' => $generatedCount . " users generated successfully. Countdown starts on first login."
        ]);
    }

    public function destroy($name)
    {
        if ($this->mikrotik->removeHotspotUser($name)) {
            return redirect()->route('hotspot.generate')->with('success', "User $name deleted successfully.");
        }
        return redirect()->route('hotspot.generate')->with('error', "Failed to delete user $name.");
    }

    private function calculateRemainingTime($user)
    {
        $comment = $user['comment'] ?? '';
        $uptime = $user['uptime'] ?? '0s';

        if (strpos($comment, 'REM:') === 0) {
            return "un-activate";
        }

        if (strpos($comment, 'EXP:') === 0) {
            $expiryStr = str_replace('EXP:', '', $comment);
            try {
                $expiry = Carbon::parse($expiryStr);
                if (Carbon::now()->greaterThan($expiry)) {
                    return "Expired";
                }
                return $expiry->diffForHumans(['parts' => 2]);
            } catch (\Exception $e) {
                return "Format Error";
            }
        }

        return "-";
    }

    private function generateDigits($length)
    {
        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= mt_rand(0, 9);
        }
        return $digits;
    }
}
