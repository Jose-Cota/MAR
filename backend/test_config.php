<?php
$config = \App\Models\MailingConfig::firstOrNew([]);
$config->host = 'smtp.office365.com';
$config->port = 587;
$config->username = 'servicio.desarrollo@tecdmx.org.mx';
$config->password = '8K7cVRbLvxz5aPBDtSzw';
$config->encryption = 'tls';
$config->from_address = 'servicio.desarrollo@tecdmx.org.mx';
$config->from_name = 'Sistema POA 2027';
$config->save();
echo "Configuracion guardada!\n";
