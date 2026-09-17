<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.167;port=3306", 'sadpyrfdbu', 'cho9r=*&prIkLyi');
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
                    
                    // Count projects
                    $stmt2 = $pdo->query("SELECT COUNT(*) FROM proyectos");
                    echo "Proyectos: " . $stmt2->fetchColumn() . "\n";
                    
                    // Verify if jose.cota exists here
                    $stmt3 = $pdo->query("SELECT usuario_poa_id, usuario, password FROM usuarios_poa WHERE usuario = 'jose.cota'");
                    $user = $stmt3->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        echo "Usuario jose.cota encontrado: \n";
                        print_r($user);
                        echo "SHA1 of J.C_2026: " . sha1('J.C_2026') . "\n";
                    } else {
                        echo "Usuario jose.cota no encontrado en esta BD.\n";
                    }
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
