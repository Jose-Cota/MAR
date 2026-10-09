<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\MailingConfig;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        $mailingConfig = MailingConfig::first();

        $defaultMailing = [
            'mail_host' => $mailingConfig?->host ?? config('mail.mailers.smtp.host', env('MAIL_HOST', 'smtp.office365.com')),
            'mail_port' => (string)($mailingConfig?->port ?? config('mail.mailers.smtp.port', env('MAIL_PORT', 587))),
            'mail_username' => $mailingConfig?->username ?? config('mail.mailers.smtp.username', env('MAIL_USERNAME', 'recursos.financieros@tecdmx.org.mx')),
            'mail_password' => $mailingConfig?->password ?? config('mail.mailers.smtp.password', env('MAIL_PASSWORD', 'F1n@nC13r0S')),
            'mail_encryption' => $mailingConfig?->encryption ?? config('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', 'tls')),
            'mail_from_address' => $mailingConfig?->from_address ?? config('mail.from.address', env('MAIL_FROM_ADDRESS', 'recursos.financieros@tecdmx.org.mx')),
            'mail_from_name' => $mailingConfig?->from_name ?? config('mail.from.name', env('MAIL_FROM_NAME', 'Sistema MAR TECDMX')),
        ];

        foreach ($defaultMailing as $key => $defaultVal) {
            if (empty($settings[$key])) {
                $settings[$key] = $defaultVal;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Sincronizar con el modelo MailingConfig si vienen configuraciones de correo
        $mailKeys = ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'];
        if ($request->hasAny($mailKeys)) {
            $mailingConfig = MailingConfig::first() ?? new MailingConfig();
            if ($request->has('mail_host')) $mailingConfig->host = $request->mail_host;
            if ($request->has('mail_port')) $mailingConfig->port = (int)$request->mail_port;
            if ($request->has('mail_username')) $mailingConfig->username = $request->mail_username;
            if ($request->filled('mail_password')) $mailingConfig->password = $request->mail_password;
            if ($request->has('mail_encryption')) $mailingConfig->encryption = $request->mail_encryption;
            if ($request->has('mail_from_address')) $mailingConfig->from_address = $request->mail_from_address;
            if ($request->has('mail_from_name')) $mailingConfig->from_name = $request->mail_from_name;
            $mailingConfig->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada correctamente'
        ]);
    }
}
