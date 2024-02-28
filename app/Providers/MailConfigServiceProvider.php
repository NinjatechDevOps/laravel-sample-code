<?php

namespace App\Providers;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $emailKeys = [
            'transport',
            'host',
            'port',
            'encryption',
            'username',
            'password',
            'address',
            'name',
        ];

        if (\Schema::hasTable('settings')) {
            $smtpSetting = DB::table('settings')->where('setting_key', 'smtp-setting')->first();
            if ($smtpSetting) {
                $smtpSettingData = json_decode($smtpSetting->setting_value, true);
                $arrayValue = json_decode($smtpSettingData, true);
                
                if (isset($arrayValue['value'])) {
                    
                    $smtpSettingValue = $arrayValue['value'];
        
                    foreach ($emailKeys as $key) {
                        if (isset($smtpSettingValue[$key])) {
                            $value = $smtpSettingValue[$key];
                            if ($key === 'address' || $key === 'name') {
                                Config::set("mail.from.{$key}", $value);
                            } else {
                                Config::set("mail.mailers.smtp.{$key}", $value);
                            }
                        }
                    }                    
                }
            }
            // dd(Config::get("mail"));
        }
        
    }



    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
