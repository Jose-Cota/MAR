<?php
$h = file_get_contents('C:\Cota\MAR\backend\_mdb.js');
file_put_contents('C:\Cota\MAR\backend\_mdb_start.txt', substr($h, 0, 5000));
