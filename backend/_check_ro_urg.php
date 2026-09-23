<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ro970 = DB::table('responsables_operativos')->where('id', 970)->first();
echo "RO 970: " . print_r($ro970, true) . "\n";

$ro1 = DB::table('responsables_operativos')->where('id', 1)->first();
echo "RO 1: " . print_r($ro1, true) . "\n";

// Get user areas
// Let's assume user id 1 or something, but we can just check urgs
$urg = DB::table('urgs')->where('urg', 'like', '%Presidencia%')->first();
echo "URG Presidencia: " . print_r($urg, true) . "\n";
