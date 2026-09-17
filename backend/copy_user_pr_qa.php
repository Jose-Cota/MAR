<?php
try {
    $pdoPR = new PDO("mysql:host=192.168.22.167;port=3306;dbname=0021sadpyrf_spagos", 'sadpyrfdbu', 'cho9r=*&prIkLyi');
    $pdoPR->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdoPR->query("SELECT * FROM usuarios WHERE email LIKE '%cota%' OR apellido1 LIKE '%cota%' OR nombre LIKE '%jose%'");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $u) {
        if (stripos($u['email'], 'cota') !== false || stripos($u['apellido1'], 'cota') !== false) {
            echo "Found user in PR ControlaTE:\n";
            print_r($u);
            
            // Connect to QA POA
            $pdoQA = new PDO("mysql:host=192.168.22.238;port=3306;dbname=0201sadpyrf_poa", 'root', 'myPass1326!');
            $pdoQA->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if he exists in QA POA
            $stmtCheck = $pdoQA->prepare("SELECT * FROM usuarios_poa WHERE usuario = 'jose.cota'");
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) {
                echo "User jose.cota already exists in QA POA.\n";
                break;
            }
            
            // Insert into QA POA
            $stmtInsert = $pdoQA->prepare("INSERT INTO usuarios_poa (area_id, nombre, apellido_paterno, apellido_materno, sexo, usuario, password, nivel) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $area_id = $u['idArea'] ? $u['idArea'] : 1; // Default to 1 if null
            $nombre = $u['nombre'];
            $apellido_paterno = $u['apellido1'];
            $apellido_materno = $u['apellido2'];
            $sexo = 'M'; // Default
            $usuario = 'jose.cota'; // Or split from email
            $password = sha1('admin123'); // Give a default password since we don't know it, or use the one from PR but it might not be sha1
            $nivel = 'administrador'; // Make him admin for ease of use
            
            $stmtInsert->execute([$area_id, $nombre, $apellido_paterno, $apellido_materno, $sexo, $usuario, $password, $nivel]);
            
            echo "Successfully inserted into QA POA as 'jose.cota' with password 'admin123' and role 'administrador'.\n";
            break;
        }
    }
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
