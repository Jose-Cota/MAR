<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.167;port=3306;dbname=0201sadpyrf_poa2025", 'sadpyrfdbu', 'cho9r=*&prIkLyi');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== meses_metas_programadas ===\n";
    $stmt = $pdo->query("DESCRIBE meses_metas_programadas");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }

    echo "=== meses_metas_alcanzadas ===\n";
    $stmt = $pdo->query("DESCRIBE meses_metas_alcanzadas");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
