<?php

namespace App\Http\Controllers;

use App\Models\RouterSetting;
use Illuminate\Http\Request;

class RouterSettingController extends Controller
{
    public function index()
    {
        $setting = RouterSetting::first();
        return view('router.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'host' => 'required',
            'username' => 'required',
            'port' => 'required|numeric',
        ]);

        $data = [
            'host' => $request->host,
            'username' => $request->username,
            'password' => $request->password, // Simpan password apa adanya
            'port' => $request->port,
        ];

        $setting = RouterSetting::first();
        
        if ($setting) {
            $setting->update($data);
        } else {
            RouterSetting::create($data);
        }

        return back()->with('success', 'Konfigurasi Mikrotik berhasil disimpan.');
    }
}