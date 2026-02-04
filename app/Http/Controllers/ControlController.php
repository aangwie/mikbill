<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\CronLog;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class ControlController extends Controller
{
    public function index()
    {
        $loginLogs = LoginLog::with('user')->latest()->limit(100)->get();
        $cronLogs = CronLog::latest()->limit(100)->get();
        $siteSetting = SiteSetting::first();

        return view('admin.control.index', compact('loginLogs', 'cronLogs', 'siteSetting'));
    }

    public function clearLoginLogs()
    {
        LoginLog::truncate();
        return back()->with('success', 'Log login berhasil dibersihkan.');
    }

    public function clearCronLogs()
    {
        CronLog::truncate();
        return back()->with('success', 'Log cronjob berhasil dibersihkan.');
    }
}
