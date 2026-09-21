<?php
$data = json_decode(file_get_contents('C:/Cota/MAR/backend/seed.json'), true);
$hasInd = [];
foreach ($data['risks'] as $r) {
    if (isset($r['indicator']) && $r['indicator']) {
        $hasInd[] = $r;
    }
}
echo json_encode(array_slice($hasInd, 0, 1), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
