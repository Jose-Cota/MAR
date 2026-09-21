<?php
$content = file_get_contents('C:/Cota/MAR/backend/routes/api.php');
$lines = explode("\n", $content);
foreach ($lines as $line) {
    if (strpos($line, 'role:') !== false) echo trim($line) . PHP_EOL;
}
