<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'like', '%karla%')->first(); 
echo "User email: " . $u->email . PHP_EOL;
echo "User role: " . $u->role . PHP_EOL;

// Get areas
$request = Illuminate\Http\Request::create('/api/unidades-responsables', 'GET');
$app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
// that might be complex, let's just use the controller or dump what the API does.

$areas = $u->unidadesResponsables;
echo "Areas assigned: " . count($areas) . PHP_EOL;
foreach($areas as $a) {
    echo "ID: " . $a->id . ", UR_ID: " . $a->unidad_responsable_gasto_id . ", Nombre: " . $a->nombre . PHP_EOL;
}
