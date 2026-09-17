<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailingConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class MailingConfigController extends Controller
{
    public function index()
    {
        $config = MailingConfig::first();
        if (!$config) {
            $config = MailingConfig::create([
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => 'tls',
            ]);
        }
        return response()->json($config);
    }

    public function update(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $config = MailingConfig::first();
        if (!$config) {
            $config = new MailingConfig();
        }

        $config->fill($request->only([
            'host', 'port', 'username', 'encryption', 'from_address', 'from_name'
        ]));

        if ($request->filled('password')) {
            $config->password = $request->password;
        }

        $config->save();

        return response()->json(['message' => 'Configuración de correo actualizada.', 'config' => $config]);
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $config = MailingConfig::first();
        if (!$config || !$config->host) {
            return response()->json(['message' => 'No hay configuración de correo válida.'], 400);
        }

        try {
            // Apply configuration dynamically
            Config::set('mail.mailers.smtp.host', $config->host);
            Config::set('mail.mailers.smtp.port', $config->port);
            Config::set('mail.mailers.smtp.encryption', $config->encryption);
            Config::set('mail.mailers.smtp.username', $config->username);
            Config::set('mail.mailers.smtp.password', $config->password);
            Config::set('mail.from.address', $config->from_address);
            Config::set('mail.from.name', $config->from_name);

            Mail::raw('Este es un correo de prueba desde el sistema POA 2027.', function ($message) use ($request, $config) {
                $message->to($request->email)
                        ->subject('Prueba de Conexión POA 2027');
            });

            return response()->json(['message' => 'Correo de prueba enviado con éxito a ' . $request->email]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al enviar correo: ' . $e->getMessage()], 500);
        }
    }
}
