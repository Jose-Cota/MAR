<?php
$code = file_get_contents('C:\Cota\MAR\frontend\src\pages\poa\POAPage.jsx');
preg_match('/api\.get\((.*?)\)/s', $code, $matches);
if(isset($matches[1])) echo $matches[1];
