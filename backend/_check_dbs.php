<?php
$mysqli = new mysqli("192.168.22.238", "root", "myPass1326!");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$dbs = ["0201sadpyrf_mar2026", "0201sadpyrf_poa", "0201sadpyrf_poa2026"];

foreach ($dbs as $db) {
    echo "=== DB: $db ===\n";
    $mysqli->select_db($db);
    $result = $mysqli->query("SHOW TABLES LIKE 'proyectos'");
    if ($result && $result->num_rows > 0) {
        $countRes = $mysqli->query("SELECT COUNT(*) as c FROM proyectos WHERE ejercicio_id = 19");
        $row = $countRes->fetch_assoc();
        echo "Proyectos 2027: " . $row['c'] . "\n";
    } else {
        echo "No proyectos table\n";
    }
}
$mysqli->close();
