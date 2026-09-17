<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$procs = DB::select('SHOW FULL PROCESSLIST');
foreach ($procs as $p) {
    if ($p->Command === 'Sleep' && $p->Time > 10 && $p->db === '0201sadpyrf_mar2026') {
        echo "Killing " . $p->Id . PHP_EOL;
        DB::statement('KILL ' . $p->Id);
    }
}
echo "Done.\n";
