<?php
$user = App\Models\User::where('usuario', 'jose.cota')->first();
echo 'User area_id: ' . $user->area_id . PHP_EOL;
$area = DB::table('unidades_responsables_gastos')->where('unidad_responsable_gasto_id', $user->area_id)->first();
if ($area) echo 'Area exists: ' . $area->nombre . PHP_EOL; else echo 'Area DOES NOT exist' . PHP_EOL;
