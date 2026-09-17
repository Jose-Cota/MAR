<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $query = "
        SELECT urg.numero, urg.nombre, COUNT(DISTINCT py.proyecto_id) as total_proyectos
        FROM proyectos py
        JOIN responsables_operativos ro ON py.responsable_operativo_id = ro.responsable_operativo_id
        JOIN unidades_responsables_gastos urg ON ro.unidad_responsable_gasto_id = urg.unidad_responsable_gasto_id
        GROUP BY urg.numero, urg.nombre
        ORDER BY urg.numero
    ";
    
    $stmt = $pdo->query($query);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "UR " . $row['numero'] . " (" . $row['nombre'] . "): " . $row['total_proyectos'] . " proyectos\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
