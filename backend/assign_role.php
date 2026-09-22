$u = App\Models\User::where('name', 'like', '%gilberto%')->first();
if ($u) {
    $u->assignRole('super administrador');
    echo "Success";
} else {
    echo "Not found";
}
