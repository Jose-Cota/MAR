<?php
$pdo = new PDO('mysql:host=192.168.22.238;dbname=0201sadpyrf_mar2026', 'root', 'Tecdmx2024*');
$stmt = $pdo->query('DESCRIBE proyectos');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
