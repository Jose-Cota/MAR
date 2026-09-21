<?php
$first = DB::table('unidades_responsables_gastos')->first();
if ($first) {
    App\Models\User::query()->update(['area_id' => $first->unidad_responsable_gasto_id]);
    echo 'Updated all users to valid area_id: ' . $first->unidad_responsable_gasto_id . PHP_EOL;
}
