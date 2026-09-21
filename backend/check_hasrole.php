<?php
$files = glob('C:/Cota/MAR/backend/app/Http/Controllers/Api/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'hasRole(') !== false) {
        echo 'Found hasRole in ' . basename($file) . PHP_EOL;
    }
}
