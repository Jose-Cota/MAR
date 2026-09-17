<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.167;port=3306;dbname=0201sadpyrf_poa2025", 'sadpyrfdbu', 'cho9r=*&prIkLyi');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $ejercicio = '2026';
    $mesId = 7;
    $query = "
        SELECT 
            py.proyecto_id,
            urg.numero as urg,
            py.numero as py,
            COALESCE(agg.total_programado, 0) as total_programado,
            COALESCE(agg.total_alcanzado, 0) as total_alcanzado
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        JOIN ejercicios ej ON urg.ejercicio_id = ej.ejercicio_id
        LEFT JOIN (
            SELECT 
                m.proyecto_id,
                SUM(mmp.cantidad) as total_programado,
                SUM(mma.cantidad) as total_alcanzado
            FROM metas m
            LEFT JOIN meses_metas_programadas mmp ON m.meta_id = mmp.meta_id AND mmp.mes_id <= ?
            LEFT JOIN meses_metas_alcanzadas mma ON m.meta_id = mma.meta_id AND mma.mes_id <= ?
            GROUP BY m.proyecto_id
        ) agg ON py.proyecto_id = agg.proyecto_id
        WHERE ej.ejercicio = ?
        GROUP BY urg.numero, py.numero
        ORDER BY urg.numero, py.numero
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$mesId, $mesId, $ejercicio]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Query returned " . count($results) . " rows.\n";
    if (count($results) > 0) {
        print_r($results[0]);
    }
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
