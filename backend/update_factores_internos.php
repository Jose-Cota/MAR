<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$area_ids = [21, 22, 23, 24, 25];
$ejercicios = [17, 19]; // 2026 and 2027

DB::beginTransaction();

try {
    foreach ($area_ids as $area_id) {
        foreach ($ejercicios as $ejercicio) {
            $risks = DB::table('riesgos')->where('area_id', $area_id)->where('ejercicio_id', $ejercicio)->get();
            foreach ($risks as $r) {
                if (in_array($r->local_id, ['R1', 'R2', 'R3'])) {
                    DB::table('riesgos')->where('id', $r->id)->update([
                        'factores_internos' => $r->factores // map it to factores_internos as well
                    ]);
                    echo "Updated {$r->local_id} for area $area_id (year $ejercicio) factores_internos.\n";
                }
            }
        }
    }
    DB::commit();
    echo "Done.\n";
} catch (\Exception $e) {
    DB::rollback();
    echo "Error: " . $e->getMessage() . "\n";
}
