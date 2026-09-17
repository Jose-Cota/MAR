<?php
$user = App\Models\User::where('usuario', 'miguel.medina')->first();
$user->load('roles', 'permissions'); 
$userArray = $user->toArray(); 
$userArray['roles'] = $user->roles->pluck('name'); 
echo json_encode($userArray['roles']);
