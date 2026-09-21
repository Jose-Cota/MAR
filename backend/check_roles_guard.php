<?php
$roles = DB::table('roles')->get();
foreach ($roles as $r) echo 'Role: ' . $r->name . ' Guard: ' . $r->guard_name . PHP_EOL;
