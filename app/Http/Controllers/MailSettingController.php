<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MailSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class MailSettingController extends Controller
{
    public function index()
    {
        $setting = MailSetting::firstOrCreate([]);
        return view('mail.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = MailSetting::firstOrCreate([]);
        $setting->update($request->all());

        return redirect()->back()->with('success', 'SMTP Settings updated successfully!');
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $setting = MailSetting::first();

        if (!$setting || !$setting->mail_host) {
            return back()->with('error', 'Please configure and save SMTP settings first.');
        }

        try {
            // Re-configure mailer on the fly
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $setting->mail_host);
            Config::set('mail.mailers.smtp.port', $setting->mail_port);
            Config::set('mail.mailers.smtp.username', $setting->mail_username);
            Config::set('mail.mailers.smtp.password', $setting->mail_password);
            Config::set('mail.mailers.smtp.encryption', $setting->mail_encryption);
            Config::set('mail.from.address', $setting->mail_from_address);
            Config::set('mail.from.name', $setting->mail_from_name);

            // Force Laravel to re-instantiate the mailer
            Mail::purge('smtp');

            $toEmail = $request->email;

            Mail::raw('This is a test email from MikBill System to verify your SMTP settings. If you receive this, your configuration is correct!', function ($message) use ($toEmail) {
                $message->to($toEmail)
                    ->subject('MikBill SMTP Connection Test');
            });

            return back()->with('success', 'Test email sent successfully to ' . $toEmail);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}