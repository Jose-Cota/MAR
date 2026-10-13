$ri = App\Models\RiesgoInstitucional::orderBy('id')->first();
$riesgo = App\Models\Riesgo::where('area_id', 1)->where('local_id', 1)->first() ?? App\Models\Riesgo::where('area_id', 1)->orderBy('id')->first();
if ($ri && $riesgo) {
    \Illuminate\Support\Facades\DB::table('riesgo_institucional_fuente')->updateOrInsert(
        ['riesgo_id' => $riesgo->id],
        ['riesgo_institucional_id' => $ri->id, 'created_at' => now(), 'updated_at' => now()]
    );
    echo "Assigned Riesgo ID " . $riesgo->id . " to RI ID " . $ri->id . "\n";
} else {
    echo "Not found\n";
}
