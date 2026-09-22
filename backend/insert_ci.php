<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$seed = json_decode(file_get_contents("C:/Cota/MAR/scratch_seed.json"), true);
$map_area = [
    "PRES" => 1,
    "SG" => 2,
    "SA" => 3,
    "DPyRF" => 4,
    "DRH" => 5,
    "DRMySG" => 6,
    "CI" => 7,
    "DGJ" => 8,
    "CCSyRP" => 9,
    "CTyDP" => 10,
    "IFyC" => 11,
    "CCLA" => 12,
    "USI" => 13,
    "UEyJ" => 14,
    "CDyP" => 15,
    "CA" => 16,
    "CDHyG" => 17,
    "DPPCyPD" => 18,
    "CVyRI" => 19,
    "UEPS" => 20,
    "PAAH" => 21,
    "PJHR" => 22,
    "POVR" => 23,
    "PKSL" => 24,
    "PLPJC" => 25
];

$c_count = 0;
$i_count = 0;

foreach ($seed["risks"] as $r) {
    if ($r["exercise"] != 2026 && $r["exercise"] != 2027) continue;
    $ej = $r["exercise"] == 2026 ? 17 : 19;
    
    $area_db_id = $map_area[$r["areaId"]] ?? null;
    if (!$area_db_id) continue;
    
    $riesgo_db = Illuminate\Support\Facades\DB::table("riesgos")
        ->where("ejercicio_id", $ej)
        ->where("area_id", $area_db_id)
        ->where("local_id", $r["localId"])
        ->first();
        
    if (!$riesgo_db) continue;
    
    // Insert controls
    if (!empty($r["controls"])) {
        foreach ($r["controls"] as $c) {
            Illuminate\Support\Facades\DB::table("riesgo_controles")->updateOrInsert(
                ["riesgo_id" => $riesgo_db->id, "texto" => $c["text"]],
                [
                    "estado_validacion" => $c["status"] ?? "Propuesto - pendiente de validación",
                    "evidencia_tipo" => $c["evidence"]["type"] ?? "",
                    "evidencia_referencia" => $c["evidence"]["reference"] ?? "",
                    "evidencia_responsable" => $c["evidence"]["responsible"] ?? "",
                    "evidencia_periodicidad" => $c["evidence"]["periodicity"] ?? ""
                ]
            );
            $c_count++;
        }
    }
    
    // Insert indicators
    if (!empty($r["indicators"])) {
        foreach ($r["indicators"] as $ind) {
            Illuminate\Support\Facades\DB::table("riesgo_indicadores")->updateOrInsert(
                ["riesgo_id" => $riesgo_db->id, "nombre" => $ind["name"]],
                [
                    "tipo" => $ind["type"] ?? "POA/Riesgo",
                    "periodicidad" => $ind["periodicity"] ?? "Trimestral",
                    "unidad" => $ind["unit"] ?? "Porcentaje",
                    "formula" => $ind["formula"] ?? "Resultado = (Atendido/Recibido)*100",
                    "numerador" => $ind["numeratorLabel"] ?? "Atendido",
                    "denominador" => $ind["denominatorLabel"] ?? "Recibido",
                    "sentido" => "Ascendente"
                ]
            );
            $i_count++;
        }
    }
}

echo "Inserted $c_count controles and $i_count indicadores.\n";

