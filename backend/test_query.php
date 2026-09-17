<?php
$pdo = new PDO('mysql:host=192.168.22.238;dbname=0201sadpyrf_poa2026', 'root', 'myPass1326!');
$proyectos_ids = [1426, 1467, 1422, 1465, 1424];

$actividades = [
    "Realizar la sustanciación, trámite y elaboración de los proyectos de resolución y/o acuerdos de asuntos jurisdiccionales, y controversias, competencia de este Tribunal.",
    "Discutir y votar los proyectos de resolución y/o acuerdos que sean sometidos a consideración del Pleno.",
    "Formular voto particular (concurrente, discrepante o aclaratorio), en caso de disentir de un proyecto de resolución aprobado por la mayoría.",
    "Elaborar y aprobar los engroses correspondientes.",
    "Concurrir, participar y votar cuando corresponda en las sesiones públicas y reuniones privadas.",
    "Participar en la integración de las Comisiones de Magistrados/as, que el Pleno determine y presentar los informes y acuerdos correspondientes.",
    "Dictaminar los proyectos de resolución y/o acuerdos presentados por las otras ponencias, así como los documentos administrativos que se ponen a consideración del Pleno.",
    "Realizar Reuniones para el análisis y revisión de los asuntos administrativos necesarios para el funcionamiento del Tribunal."
];

try {
    $pdo->beginTransaction();

    foreach ($proyectos_ids as $p_id) {
        // Borrar las existentes
        $stmt_del = $pdo->prepare("DELETE FROM actividades_sustantivas WHERE proyecto_id = ?");
        $stmt_del->execute([$p_id]);
        
        // Insertar las nuevas
        $stmt_ins = $pdo->prepare("INSERT INTO actividades_sustantivas (proyecto_id, numero, descripcion) VALUES (?, ?, ?)");
        
        $num = 1;
        foreach ($actividades as $act) {
            $stmt_ins->execute([$p_id, $num, $act]);
            $num++;
        }
    }

    $pdo->commit();
    echo "¡Listo! Se actualizaron las nuevas 8 actividades para los 5 proyectos de 2027.";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
