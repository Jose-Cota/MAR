<?php
use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    echo json_encode($t) . "\n";
}
