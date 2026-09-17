<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notificación de Proyecto POA</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #1F4E79; text-align: center;">Sistema POA 2027</h2>
        <p>Hola,</p>
        <p>Te informamos que la Ficha de Proyecto <strong>{{ $proyecto->numero }} - {{ $proyecto->nombre }}</strong> ha cambiado de estatus a <strong>{{ $proyecto->status }}</strong>.</p>
        
        @if(!empty($mensajeBitacora))
            <div style="background-color: #f4f6f8; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Mensaje adjunto:</strong></p>
                <p style="margin: 5px 0 0 0;">{{ $mensajeBitacora }}</p>
            </div>
        @endif

        <p>Por favor, ingresa al Sistema POA 2027 para revisarlo y tomar las acciones correspondientes.</p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ env('FRONTEND_URL', 'https://poa2027.tecdmx.org.mx') }}" style="background-color: #1F4E79; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Acceder al Sistema</a>
        </div>
        
        <hr style="border: none; border-top: 1px solid #eee; margin-top: 30px;">
        <p style="font-size: 12px; color: #888; text-align: center;">Este es un correo generado automáticamente por el Sistema POA. Por favor, no respondas a este mensaje.</p>
    </div>
</body>
</html>
