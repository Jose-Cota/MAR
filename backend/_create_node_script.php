<?php
$h = file_get_contents('C:\Cota\MAR\Sistema_MAR_TECDMX_2026_2027_v7_7.html');
$pos = strpos($h, 'M.db=');
if ($pos === false) $pos = strpos($h, 'const db={');
if ($pos === false) $pos = strpos($h, 'const db = {');

if ($pos !== false) {
    // Find the end of the db object (before return db)
    $startPos = strpos($h, '{', $pos);
    $endPos = strpos($h, '};', $startPos);
    $jsObj = substr($h, $startPos, $endPos - $startPos + 1);
    
    $jsScript = "const db = " . $jsObj . ";\nconsole.log(JSON.stringify(db));\n";
    file_put_contents('C:\Cota\MAR\backend\_parse_db.js', $jsScript);
    echo "Wrote Node script\n";
} else {
    echo "Could not find db def\n";
}
