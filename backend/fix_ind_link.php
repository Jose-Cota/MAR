<?php
use Illuminate\Support\Facades\DB;

// Update the indicator to link to the Meta Complementaria so the frontend displays it
$updated = DB::table('indicadores')->where('indicador_id', 7922)->update(['meta_id' => 7215]);

echo "Update result: $updated rows updated.\n";
exit;
