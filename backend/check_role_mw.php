<?php
$content = file_get_contents('C:/Cota/MAR/backend/routes/api.php');
if (strpos($content, 'role:') !== false) echo 'Has role middleware!' . PHP_EOL;
else echo 'No role middleware found.' . PHP_EOL;
