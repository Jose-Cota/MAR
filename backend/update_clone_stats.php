<?php
$file = 'C:/Cota/POA/backend/app/Http/Controllers/Api/ModoController.php';
$content = file_get_contents($file);

// Replace early returns with return $stats
$content = str_replace(
    'if (!$oldEjercicio) return;',
    'if (!$oldEjercicio) return $stats;',
    $content
);
$content = str_replace(
    'if ($hasUrg || $hasPg || $hasUm) return;',
    'if ($hasUrg || $hasPg || $hasUm) return $stats;',
    $content
);
$content = str_replace(
    'if (empty($mapPg)) return;',
    'if (empty($mapPg)) return $stats;',
    $content
);
$content = str_replace(
    'if (empty($mapUrg)) return;',
    'if (empty($mapUrg)) return $stats;',
    $content
);
$content = str_replace(
    'if (empty($mapRo) || empty($mapSp)) return;',
    'if (empty($mapRo) || empty($mapSp)) return $stats;',
    $content
);
$content = preg_replace(
    '/if \(empty\(\$mapPy\)\) return;/',
    'if (empty($mapPy)) return $stats;',
    $content,
    1 // replace only the one for early return in cloning
);

// Add initialization of $stats at the beginning of clonePreviousYearData
$content = str_replace(
    '    private function clonePreviousYearData($nuevoEjercicioId, $nuevoEjercicioAnio)
    {',
    '    private function clonePreviousYearData($nuevoEjercicioId, $nuevoEjercicioAnio)
    {
        $stats = [
            \'unidades_medidas\' => 0, \'programas\' => 0, \'subprogramas\' => 0,
            \'unidades_responsables_gastos\' => 0, \'responsables_operativos\' => 0, \'proyectos\' => 0, \'metas\' => 0,
            \'meses_metas_programadas\' => 0, \'actividades_sustantivas\' => 0, \'indicadores\' => 0, \'pei_proyecto_alineaciones\' => 0
        ];',
    $content
);

// Update $stats for Unidades Medida
$content = preg_replace(
    '/foreach \(\$unidades_medidas as \$um\) \{/',
    '$stats[\'unidades_medidas\'] = count($unidades_medidas);
        foreach ($unidades_medidas as $um) {',
    $content
);

// Update $stats for Programas
$content = preg_replace(
    '/foreach \(\$programas as \$p\) \{/',
    '$stats[\'programas\'] = count($programas);
        foreach ($programas as $p) {',
    $content
);

// Update $stats for Subprogramas
$content = preg_replace(
    '/foreach \(\$subprogramas as \$sp\) \{/',
    '$stats[\'subprogramas\'] = count($subprogramas);
        foreach ($subprogramas as $sp) {',
    $content
);

// Update $stats for URGs
$content = preg_replace(
    '/foreach \(\$urgs as \$urg\) \{/',
    '$stats[\'unidades_responsables_gastos\'] = count($urgs);
        foreach ($urgs as $urg) {',
    $content
);

// Update $stats for ROs
$content = preg_replace(
    '/foreach \(\$ros as \$ro\) \{/',
    '$stats[\'responsables_operativos\'] = count($ros);
        foreach ($ros as $ro) {',
    $content
);

// We need to count mapped Py and mapped Alineaciones inside the loop
$content = preg_replace(
    '/\$proyectos = DB::connection\(\'poa_prod\'\)->table\(\'proyectos\'\)->whereIn\(\'responsable_operativo_id\', array_keys\(\$mapRo\)\)->get\(\);/',
    '$proyectos = DB::connection(\'poa_prod\')->table(\'proyectos\')->whereIn(\'responsable_operativo_id\', array_keys($mapRo))->get();
        $clonedProjects = 0;
        $clonedAlineaciones = 0;',
    $content
);

$content = str_replace(
    '$mapPy[$py->proyecto_id] = $newId;',
    '$mapPy[$py->proyecto_id] = $newId;
            $clonedProjects++;',
    $content
);

$content = str_replace(
    'pei_objetivo_estrategico_id\' => $newObjId,
                        ]);',
    'pei_objetivo_estrategico_id\' => $newObjId,
                        ]);
                        $clonedAlineaciones++;',
    $content
);

$content = str_replace(
    'if (empty($mapPy)) return $stats;',
    '$stats[\'proyectos\'] = $clonedProjects;
        $stats[\'pei_proyecto_alineaciones\'] = $clonedAlineaciones;

        if (empty($mapPy)) return $stats;',
    $content
);

$content = str_replace(
    '        if (!empty($insertsAct)) {
            foreach (array_chunk($insertsAct, 100) as $chunk) {
                DB::connection(\'poa_prod\')->table(\'actividades_sustantivas\')->insert($chunk);
            }
        }',
    '        if (!empty($insertsAct)) {
            $stats[\'actividades_sustantivas\'] = count($insertsAct);
            foreach (array_chunk($insertsAct, 100) as $chunk) {
                DB::connection(\'poa_prod\')->table(\'actividades_sustantivas\')->insert($chunk);
            }
        }',
    $content
);

$content = str_replace(
    '        if (!empty($insertsMeses)) {
            foreach (array_chunk($insertsMeses, 100) as $chunk) {
                DB::connection(\'poa_prod\')->table(\'meses_metas_programadas\')->insert($chunk);
            }
        }',
    '        if (!empty($insertsMeses)) {
            $stats[\'meses_metas_programadas\'] = count($insertsMeses);
            foreach (array_chunk($insertsMeses, 100) as $chunk) {
                DB::connection(\'poa_prod\')->table(\'meses_metas_programadas\')->insert($chunk);
            }
        }',
    $content
);

$content = str_replace(
    '            if (!empty($insertsInds)) {
                foreach (array_chunk($insertsInds, 100) as $chunk) {
                    DB::connection(\'poa_prod\')->table(\'indicadores\')->insert($chunk);
                }
            }',
    '            if (!empty($insertsInds)) {
                $stats[\'indicadores\'] = count($insertsInds);
                foreach (array_chunk($insertsInds, 100) as $chunk) {
                    DB::connection(\'poa_prod\')->table(\'indicadores\')->insert($chunk);
                }
            }',
    $content
);

$content = str_replace(
    '$mapMeta[$m->meta_id] = $newMetaId;',
    '$mapMeta[$m->meta_id] = $newMetaId;
            $stats[\'metas\']++;',
    $content
);

$content = str_replace(
    '    }
}
',
    '        return $stats;
    }
}
',
    $content
);

file_put_contents($file, $content);
echo "Done";
