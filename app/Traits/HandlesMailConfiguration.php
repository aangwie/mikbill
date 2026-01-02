<?php

namespace App\Traits;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

trait HandlesMailConfiguration
{
    /**
     * Apply SMTP settings from database to the current process.
     * Use this before sending any email.
     */
    protected function applyMailConfig()
    {
        $setting = MailSetting::first();

        if ($setting && $setting->mail_host) {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $setting->mail_host);
            Config::set('mail.mailers.smtp.port', $setting->mail_port);
            Config::set('mail.mailers.smtp.username', $setting->mail_username);
            Config::set('mail.mailers.smtp.password', $setting->mail_password);
            Config::set('mail.mailers.smtp.encryption', $setting->mail_encryption);
            Config::set('mail.from.address', $setting->mail_from_address);
            Config::set('mail.from.name', $setting->mail_from_name);

            // Force Laravel to re-instantiate the mailer with new configuration
            Mail::purge('smtp');
        }
    }
}
