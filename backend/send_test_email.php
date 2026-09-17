<?php

try {
    $config = \App\Models\MailingConfig::first();
    if (!$config || !$config->host) {
        die("No hay configuracion de mailing en la base de datos.\n");
    }

    \Illuminate\Support\Facades\Config::set('mail.default', 'smtp');
    \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.host', $config->host);
    \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.port', $config->port);
    \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.encryption', $config->encryption);
    \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.username', $config->username);
    \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.password', $config->password);
    \Illuminate\Support\Facades\Config::set('mail.from.address', $config->from_address);
    \Illuminate\Support\Facades\Config::set('mail.from.name', $config->from_name);

    $proyectoObj = (object)[
        'numero' => 'TEST-00',
        'nombre' => 'Proyecto de Prueba de Correo',
        'status' => 'Verificado/Validación'
    ];

    $mensajeBitacora = "Esta es una prueba de envío de correo generada manualmente para validar que la configuración SMTP esté funcionando correctamente con los correos @tecdmx.org.mx recién actualizados.";

    $emails = [
        'jose.cota@tecdmx.org.mx',
        'fernando.cortes@tecdmx.org.mx',
        'gilberto.gomez@tecdmx.org.mx'
    ];

    foreach ($emails as $email) {
        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\ProyectoNotificacionMail($proyectoObj, $mensajeBitacora));
        echo "Correo enviado exitosamente a $email.\n";
    }


} catch (\Exception $e) {
    echo "Error al enviar correo: " . $e->getMessage() . "\n";
}
