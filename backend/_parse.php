<?php
$content = file_get_contents('out.json');
$jsonStr = substr($content, strpos($content, '['));
$data = json_decode($jsonStr, true);
echo count($data) . " projects returned\n";
