<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = 'Tables_in_' . env('DB_DATABASE');

$allTables = [];
foreach($tables as $t) {
    $tableName = $t->{$dbName} ?? array_values((array)$t)[0];
    $allTables[] = $tableName;
}

// Gather all PHP files in app/ and routes/
$files = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/app'));
foreach($iter as $file) {
    if($file->getExtension() === 'php') $files[] = $file->getPathname();
}
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/routes'));
foreach($iter as $file) {
    if($file->getExtension() === 'php') $files[] = $file->getPathname();
}

$contents = '';
foreach($files as $f) {
    $contents .= file_get_contents($f) . "\n";
}

$unused = [];
foreach($allTables as $t) {
    // If it's a migration/jobs default table, we know it's used by Laravel
    if(in_array($t, ['migrations', 'failed_jobs', 'jobs', 'job_batches', 'personal_access_tokens', 'password_reset_tokens', 'cache', 'cache_locks', 'sessions'])) {
        continue;
    }
    
    // Check if table name is in contents
    // We add simple quotes checks or just plain strpos
    if(strpos($contents, "'" . $t . "'") === false && strpos($contents, '"' . $t . '"') === false && strpos($contents, $t) === false) {
        // Double check model conventions (e.g. 'acciones_sustantivas' -> AccionSustantiva)
        // Actually if $t is not even in the strings, it's highly likely unused.
        $unused[] = $t;
    }
}

echo "Tablas aparentemente NO utilizadas en el código (App/Routes):\n\n";
foreach($unused as $u) {
    echo "- $u\n";
}
