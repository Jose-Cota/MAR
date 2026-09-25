<?php
require 'backend/_db.php';
$stmt = $pdo->prepare("UPDATE riesgos SET status = 'Captura' WHERE status = 'Borrador' AND area_id IN (SELECT id_unidad FROM cat_unidades_responsables WHERE denominacion LIKE '%archivo%')");
$stmt->execute();
echo $stmt->rowCount() . ' rows updated.';
?>
