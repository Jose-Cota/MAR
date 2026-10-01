<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$area_ids = [21, 22, 23, 24, 25];
$ejercicio = 19; // 2027

DB::beginTransaction();

try {
    foreach ($area_ids as $area_id) {
        echo "Processing area_id $area_id for 2027\n";
        
        $risks = DB::table('riesgos')->where('area_id', $area_id)->where('ejercicio_id', $ejercicio)->get();
        
        // We assume local_id are R1, R2, R3, R4, R5
        foreach ($risks as $r) {
            if ($r->local_id === 'R4' || $r->local_id === 'R5') {
                DB::table('riesgo_controles')->where('riesgo_id', $r->id)->delete();
                DB::table('riesgo_indicadores')->where('riesgo_id', $r->id)->delete();
                DB::table('riesgos')->where('id', $r->id)->delete();
                echo "  Deleted {$r->local_id}\n";
            } else if ($r->local_id === 'R1') {
                DB::table('riesgos')->where('id', $r->id)->update([
                    'riesgo' => 'Que la carga de asuntos y los plazos aplicables afecten la sustanciación, elaboración y resolución oportuna de los asuntos turnados a la Ponencia.',
                    'factores' => 'Incremento extraordinario de asuntos y medios de impugnación; plazos breves.',
                    'probabilidad' => 1,
                    'impacto' => 10,
                    'probabilidad_inicial' => 1,
                    'impacto_inicial' => 10
                ]);
                DB::table('riesgo_controles')->where('riesgo_id', $r->id)->update(['texto' => 'Distribución equitativa de cargas de trabajo.']);
                DB::table('riesgo_indicadores')->where('riesgo_id', $r->id)->update(['formula' => 'Resultado = (Asuntos atendidos oportunamente / Asuntos recibidos) × 100']);
                echo "  Updated R1\n";
            } else if ($r->local_id === 'R2') {
                DB::table('riesgos')->where('id', $r->id)->update([
                    'riesgo' => 'Que la Ponencia no cuente oportunamente con análisis o documentación suficiente para deliberar y votar.',
                    'factores' => 'Circulación tardía y fallas tecnológicas.',
                    'probabilidad' => 2,
                    'impacto' => 9,
                    'probabilidad_inicial' => 2,
                    'impacto_inicial' => 9
                ]);
                DB::table('riesgo_controles')->where('riesgo_id', $r->id)->update(['texto' => 'Agenda de sesiones y reuniones; revisión previa de asuntos; control de documentos circulados']);
                DB::table('riesgo_indicadores')->where('riesgo_id', $r->id)->update(['formula' => 'Resultado = (Votaciones sin incidencias / Total de asuntos votados) × 100.']);
                echo "  Updated R2\n";
            } else if ($r->local_id === 'R3') {
                DB::table('riesgos')->where('id', $r->id)->update([
                    'riesgo' => 'Que las comisiones, informes, acuerdos o asuntos administrativos asignados a la Ponencia no sean atendidos oportunamente.',
                    'factores' => 'Información dispersa o incompleta.',
                    'probabilidad' => 2,
                    'impacto' => 8,
                    'probabilidad_inicial' => 2,
                    'impacto_inicial' => 8
                ]);
                DB::table('riesgo_controles')->where('riesgo_id', $r->id)->update(['texto' => 'Registro de encargos y acuerdos; responsables y fechas compromiso; integración de antecedentes; consulta a áreas competentes; control de entregables y seguimiento de pendientes.']);
                DB::table('riesgo_indicadores')->where('riesgo_id', $r->id)->update(['formula' => 'Resultado = (Comisiones y asuntos administrativos atendidos dentro del plazo o fecha compromiso / Total de comisiones y asuntos administrativos sujetos a atención) × 100.']);
                echo "  Updated R3\n";
            }
        }
    }
    DB::commit();
    echo "Successfully updated the database for 2027.\n";
} catch (\Exception $e) {
    DB::rollback();
    echo "Error updating the database: " . $e->getMessage() . "\n";
}
