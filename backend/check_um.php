<?php
use Illuminate\Support\Facades\DB;

$schema = DB::select("DESCRIBE unidades_medidas");
echo json_encode($schema, JSON_PRETTY_PRINT);
exit;
