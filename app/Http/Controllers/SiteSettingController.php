<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function index()
    {
        $setting = SiteSetting::first();
        if (!$setting) {
            $setting = SiteSetting::create([
                'about_us' => 'Selamat datang di layanan kami.',
                'terms_conditions' => 'Syarat dan ketentuan berlaku.'
            ]);
        }
        return view('superadmin.settings.site', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = SiteSetting::first();
        $setting->update($request->only(['about_us', 'terms_conditions']));

        return back()->with('success', 'Informasi situs berhasil diperbarui.');
    }
}
