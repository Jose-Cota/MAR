<?php
$j = json_decode(file_get_contents('_fichas_10.json'), true);
foreach($j as $p) {
    echo "{$p['proyecto_id']} - {$p['nombre']} (urg: {$p['urg_id']}, ro: {$p['responsable_operativo_id']})\n";
}
