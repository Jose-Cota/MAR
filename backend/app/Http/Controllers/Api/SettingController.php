<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\MailingConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Asegura que la tabla settings exista en la base de datos.
     */
    protected function ensureSettingsTableExists(): void
    {
        try {
            if (!Schema::hasTable('settings')) {
                Schema::create('settings', function (Blueprint $table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->text('value')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo verificar/crear la tabla settings: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $this->ensureSettingsTableExists();

        $settings = [];
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::pluck('value', 'key')->toArray();
            }
        } catch (\Throwable $e) {
            Log::warning('Error al obtener settings: ' . $e->getMessage());
            $settings = [];
        }

        // Determinar si el usuario autenticado tiene permisos de Administrador
        $isAdmin = false;
        try {
            $user = $request->user('sanctum');
            if ($user) {
                if (method_exists($user, 'hasRole')) {
                    $isAdmin = $user->hasRole('Administrador') || $user->hasRole('Super Administrador');
                }
                if (!$isAdmin && isset($user->roles)) {
                    $isAdmin = $user->roles->contains(function ($r) {
                        return in_array($r->name, ['Administrador', 'Super Administrador']);
                    });
                }
                if (!$isAdmin && isset($user->nivel) && (int)$user->nivel >= 3) {
                    $isAdmin = true;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Error verificando roles de usuario en SettingController: ' . $e->getMessage());
            $isAdmin = false;
        }

        if ($isAdmin) {
            $mailingConfig = null;
            try {
                if (class_exists(MailingConfig::class) && Schema::hasTable('mailing_configs')) {
                    $mailingConfig = MailingConfig::first();
                }
            } catch (\Throwable $e) {
                Log::warning('Error consultando MailingConfig: ' . $e->getMessage());
            }

            // Descifrado seguro del password de correo para evitar DecryptException si la APP_KEY cambió
            $mailPassword = config('mail.mailers.smtp.password', env('MAIL_PASSWORD', 'F1n@nC13r0S'));
            if ($mailingConfig) {
                try {
                    $mailPassword = $mailingConfig->password ?? $mailPassword;
                } catch (\Throwable $e) {
                    Log::warning('Error descifrando mailingConfig password: ' . $e->getMessage());
                }
            }

            $defaultMailing = [
                'mail_host' => $mailingConfig?->host ?? config('mail.mailers.smtp.host', env('MAIL_HOST', 'smtp.office365.com')),
                'mail_port' => (string)($mailingConfig?->port ?? config('mail.mailers.smtp.port', env('MAIL_PORT', 587))),
                'mail_username' => $mailingConfig?->username ?? config('mail.mailers.smtp.username', env('MAIL_USERNAME', 'recursos.financieros@tecdmx.org.mx')),
                'mail_password' => $mailPassword,
                'mail_encryption' => $mailingConfig?->encryption ?? config('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', 'tls')),
                'mail_from_address' => $mailingConfig?->from_address ?? config('mail.from.address', env('MAIL_FROM_ADDRESS', 'recursos.financieros@tecdmx.org.mx')),
                'mail_from_name' => $mailingConfig?->from_name ?? config('mail.from.name', env('MAIL_FROM_NAME', 'Sistema MAR TECDMX')),
            ];

            foreach ($defaultMailing as $key => $defaultVal) {
                if (empty($settings[$key])) {
                    $settings[$key] = $defaultVal;
                }
            }
        } else {
            unset($settings['mail_password'], $settings['mail_username'], $settings['mail_host'], $settings['mail_port']);
        }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureSettingsTableExists();

        try {
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
                try {
                    if (Schema::hasTable('mailing_configs')) {
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
                } catch (\Throwable $e) {
                    Log::warning('Error sincronizando con MailingConfig: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada correctamente'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en SettingController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }
}
