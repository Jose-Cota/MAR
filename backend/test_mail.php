<?php
$config = \App\Models\MailingConfig::first();
\Illuminate\Support\Facades\Config::set('mail.mailers.smtp.host', $config->host);
\Illuminate\Support\Facades\Config::set('mail.mailers.smtp.port', $config->port);
\Illuminate\Support\Facades\Config::set('mail.mailers.smtp.encryption', $config->encryption);
\Illuminate\Support\Facades\Config::set('mail.mailers.smtp.username', $config->username);
\Illuminate\Support\Facades\Config::set('mail.mailers.smtp.password', $config->password);
\Illuminate\Support\Facades\Config::set('mail.from.address', $config->from_address);
\Illuminate\Support\Facades\Config::set('mail.from.name', $config->from_name);

\Illuminate\Support\Facades\Mail::raw('Este es un correo de prueba de configuracion SMTP desde el Sistema POA 2027.', function ($message) {
    $message->to('jose.cota@tecdmx.org.mx')
            ->subject('Prueba de conexion SMTP');
});
echo "Email enviado exitosamente!\n";
