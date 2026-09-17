<?php
use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
echo json_encode($tables, JSON_PRETTY_PRINT);
