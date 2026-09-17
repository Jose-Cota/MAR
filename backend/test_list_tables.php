<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $start = microtime(true);
    $mesId = 6; // Junio
    
    $query = "
        SELECT 
            urg.numero as urg,
            py.numero as py,
            COALESCE(SUM(agg.total_programado), 0) as total_programado,
            COALESCE(SUM(agg.total_alcanzado), 0) as total_alcanzado
        FROM proyectos as py
        JOIN responsables_operativos as ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos as urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        LEFT JOIN (
            SELECT 
                m.proyecto_id,
                COALESCE(SUM(mmp.numero), 0) as total_programado,
                COALESCE(SUM(mma.numero), 0) as total_alcanzado
            FROM metas m
            LEFT JOIN (
                SELECT meta_id, SUM(numero) as numero 
                FROM meses_metas_programadas 
                WHERE mes_id <= ? 
                GROUP BY meta_id
            ) mmp ON m.meta_id = mmp.meta_id
            LEFT JOIN (
                SELECT meta_id, SUM(numero) as numero 
                FROM meses_metas_alcanzadas 
                WHERE mes_id <= ? 
                GROUP BY meta_id
            ) mma ON m.meta_id = mma.meta_id
            GROUP BY m.proyecto_id
        ) agg ON py.proyecto_id = agg.proyecto_id
        GROUP BY urg.numero, py.numero
        ORDER BY urg.numero, py.numero
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$mesId, $mesId]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $time = microtime(true) - $start;
    echo "Query finished in " . round($time, 3) . " seconds. Rows: " . count($res) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
