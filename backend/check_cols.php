<?php
use Illuminate\Support\Facades\DB;

$item = DB::table('unidades_responsables_gastos')->first();
if ($item) {
    echo json_encode(array_keys((array)$item)) . "\n";
} else {
    echo "Table empty.\n";
}
