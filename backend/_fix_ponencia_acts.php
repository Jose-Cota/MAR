<?php
try {
    $pdo = new PDO("mysql:host=192.168.22.238", "root", "myPass1326!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE `0201sadpyrf_mar2026`");

    $proys1 = [1609, 1611, 1613, 1615, 1617];
    foreach ($proys1 as $p1) {
        $p2 = $p1 + 1;
        $stmt = $pdo->prepare("SELECT id, descripcion FROM actividades_sustantivas WHERE proyecto_id = ? ORDER BY id ASC");
        $stmt->execute([$p1]);
        $acts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "P1: $p1 has " . count($acts) . " acts. Moving second half to $p2...\n";
        
        $half = count($acts) / 2;
        if ($half == 8) {
            for ($i = 8; $i < 16; $i++) {
                $actId = $acts[$i]['id'];
                echo "  Moving Act $actId (" . substr($acts[$i]['descripcion'], 0, 20) . ")\n";
                // Only uncomment this when ready
                $update = $pdo->prepare("UPDATE actividades_sustantivas SET proyecto_id = ? WHERE id = ?");
                $update->execute([$p2, $actId]);
            }
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
