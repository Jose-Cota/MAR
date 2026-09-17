<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238;port=3306", 'root', 'myPass1326!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $dbs = ['0201sadpyrf_poa', '0201sadpyrf_poa2025', '0202tedf_sadpyrf_poa'];
    
    foreach ($dbs as $db) {
        echo "=== Buscando en base de datos: $db ===\n";
        try {
            $pdo->exec("USE $db");
            $stmt = $pdo->query("SELECT * FROM ejercicios");
            $found = false;
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (trim($row['ejercicio']) == '2026') {
                    echo "¡ENCONTRADO! Ejercicio ID: " . $row['ejercicio_id'] . " | Año: " . $row['ejercicio'] . "\n";
                    $found = true;
                }
            }
            if (!$found) {
                echo "No se encontró 2026 en la tabla ejercicios de $db.\n";
            }
        } catch (Exception $ex) {
            echo "Error al acceder a $db: " . $ex->getMessage() . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
