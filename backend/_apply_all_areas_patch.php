<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$labels = [
  'SA-2026-A1'=>'Coordinación de la administración de recursos humanos, materiales y financieros',
  'SA-2026-A2'=>'Informes institucionales, planeación, POA, presupuesto y seguimiento de auditorías',
  'DRH-2026-A1'=>'Nómina, remuneraciones, prestaciones y enteros',
  'DRH-2026-A2'=>'Movimientos de personal y seguridad social',
  'DRH-2026-A3'=>'Expedientes, nombramientos, constancias y documentación del personal',
  'DRH-2026-A4'=>'Servicios de salud y administración de seguros del personal',
  'DRH-2026-A5'=>'Normatividad, procedimientos, convenios y servicios al personal',
  'DRH-2026-A6'=>'Información institucional, estadística y atención de requerimientos de Recursos Humanos',
  'DRMySG-2026-A1'=>'Programa y procedimientos de adquisiciones',
  'DRMySG-2026-A2'=>'Arrendamientos y contratación de bienes y servicios',
  'DRMySG-2026-A3'=>'Servicios generales y contratación de servicios institucionales',
  'DRMySG-2026-A4'=>'Almacén, inventarios y movimientos de bienes',
  'DRMySG-2026-A5'=>'Mantenimiento e infraestructura institucional',
  'DRMySG-2026-A6'=>'Protección civil y capacitación de brigadas',
  'DRMySG-2026-A7'=>'Transparencia, solicitudes de información y atención de auditorías',
  'CI-2026-A1'=>'Informes trimestrales, anuales y requeridos por entes públicos',
  'CI-2026-A2'=>'Investigación y procedimientos de responsabilidad administrativa',
  'CI-2026-A3'=>'Prevención, responsabilidades, declaraciones patrimoniales y verificaciones',
  'CI-2026-A4'=>'Auditorías, informes finales y seguimiento de observaciones',
  'CI-2026-A5'=>'Asesoría, acompañamiento, integridad y prevención de actos de corrupción',
  'DGJ-2026-A1'=>'Representación y defensa jurídica del Tribunal',
  'DGJ-2026-A2'=>'Atención de asuntos contenciosos y procedimientos jurisdiccionales',
  'DGJ-2026-A3'=>'Consultas, asesoría y opiniones jurídicas',
  'DGJ-2026-A4'=>'Elaboración, análisis y revisión de normativa',
  'DGJ-2026-A5'=>'Convenios, actos jurídicos y compromisos institucionales',
  'DGJ-2026-A6'=>'Contratos e instrumentos jurídicos',
  'CCSyRP-2026-A1'=>'Posicionamiento y comunicación institucional',
  'CCSyRP-2026-A2'=>'Relaciones públicas, cobertura y eventos institucionales',
  'CCSyRP-2026-A3'=>'Elaboración y difusión de información y contenidos institucionales',
  'CTyDP-2026-A1'=>'Solicitudes de acceso a la información y recursos',
  'CTyDP-2026-A2'=>'Sesiones, acuerdos y seguimiento del Comité de Transparencia',
  'CTyDP-2026-A3'=>'Protección y tratamiento de datos personales',
  'CTyDP-2026-A4'=>'Derechos ARCO y procedimientos vinculados',
  'CTyDP-2026-A5'=>'Obligaciones de transparencia, asesoría y capacitación especializada',
  'IFyC-2026-A1'=>'Programa y acciones institucionales de capacitación',
  'IFyC-2026-A2'=>'Cursos, talleres y actividades formativas',
  'IFyC-2026-A3'=>'Mecanismos de educación, formación y profesionalización',
  'CCLA-2026-A1'=>'Sustanciación de controversias laborales e inconformidades administrativas',
  'CCLA-2026-A2'=>'Consultas y asesoría en materia laboral y administrativa',
  'CCLA-2026-A3'=>'Normativa, criterios y actualización de instrumentos aplicables',
  'USI-2026-A1'=>'Desarrollo y modernización de procesos y servicios informáticos',
  'USI-2026-A2'=>'Infraestructura tecnológica y continuidad de servicios críticos',
  'USI-2026-A3'=>'Soporte técnico, atención y seguimiento de incidencias',
  'USI-2026-A4'=>'Seguridad de la información y controles tecnológicos',
  'USI-2026-A5'=>'Respaldos, recuperación y continuidad tecnológica',
  'USI-2026-A6'=>'Desarrollo, mantenimiento y actualización de sistemas institucionales',
  'UEyJ-2026-A1'=>'Jurisprudencia, tesis, precedentes y criterios jurisdiccionales',
  'UEyJ-2026-A2'=>'Sistematización de información jurisdiccional',
  'UEyJ-2026-A3'=>'Estadística jurisdiccional',
  'UEyJ-2026-A4'=>'Informes, consultas y productos estadísticos',
  'CDyP-2026-A1'=>'Obras, publicaciones y materiales editoriales',
  'CDyP-2026-A2'=>'Biblioteca, acervo y servicios documentales',
  'CDyP-2026-A3'=>'Producción y gestión de publicaciones institucionales',
  'CDyP-2026-A4'=>'Difusión, publicación y distribución de contenidos',
  'CDyP-2026-A5'=>'Diseño y producción de materiales gráficos y audiovisuales',
  'CA-2026-A1'=>'Programa Anual de Desarrollo Archivístico (PADA)',
  'CA-2026-A2'=>'Instrumentos de control y consulta archivística',
  'CA-2026-A3'=>'Gestión, integración, conservación y control documental',
  'CA-2026-A4'=>'Orientación y capacitación en materia archivística',
  'CA-2026-A5'=>'Normativa y funcionamiento del Sistema Institucional de Archivos',
  'CA-2026-A6'=>'Patrimonio documental y conservación de archivos',
  'CDHyG-2026-A1'=>'Política institucional de derechos humanos e igualdad',
  'CDHyG-2026-A2'=>'Transversalización de derechos humanos y perspectiva de género',
  'CDHyG-2026-A3'=>'Comités, grupos y mecanismos institucionales de igualdad',
  'CDHyG-2026-A4'=>'Capacitación y sensibilización en derechos humanos y género',
  'CDHyG-2026-A5'=>'Difusión y promoción de derechos humanos, igualdad y no discriminación',
  'DPPCyPD-2026-A1'=>'Asesoría y defensa de derechos político-electorales',
  'DPPCyPD-2026-A2'=>'Difusión, orientación y acercamiento sobre derechos político-electorales',
  'DPPCyPD-2026-A3'=>'Registro, seguimiento e informes de servicios y defensas',
  'DPPCyPD-2026-A4'=>'Coordinación y cobertura de acciones de atención y defensa',
  'CVyRI-2026-A1'=>'Convenios y mecanismos de colaboración institucional',
  'CVyRI-2026-A2'=>'Vinculación nacional e internacional',
  'CVyRI-2026-A3'=>'Eventos y actividades de vinculación',
  'CVyRI-2026-A4'=>'Relaciones interinstitucionales y seguimiento de compromisos',
  'UEPS-2026-A1'=>'Estudio y elaboración de proyectos en procedimientos especiales sancionadores',
  'UEPS-2026-A2'=>'Elaboración de proyectos, acuerdos y actuaciones de los procedimientos',
  'UEPS-2026-A3'=>'Seguimiento de impugnaciones e integridad de expedientes y documentación',
  'SG-2026-A1'=>'Recepción, registro, turno e integración de expedientes jurisdiccionales',
  'SG-2026-A2'=>'Diligencias, notificaciones y actuaciones ordenadas',
  'SG-2026-A3'=>'Apoyo técnico-jurídico al Pleno, sesiones, actas, acuerdos y certificaciones',
];

// Map acronyms to DB 'nombre' partial match
$urgMap = [
    'SA' => 'Secretaría Administrativa',
    'DRH' => 'Recursos Humanos',
    'DRMySG' => 'Recursos Materiales',
    'CI' => 'Contraloría Interna',
    'DGJ' => 'Dirección General Jurídica',
    'CCSyRP' => 'Comunicación Social',
    'CTyDP' => 'Transparencia',
    'IFyC' => 'Instituto de Formación',
    'CCLA' => 'Controversias Laborales',
    'USI' => 'Sistemas Informáticos',
    'UEyJ' => 'Estadística y Jurisprudencia',
    'CDyP' => 'Difusión y Publicaciones',
    'CA' => 'Coordinación de Archivo',
    'CDHyG' => 'Derechos Humanos y Género',
    'DPPCyPD' => 'Defensoría',
    'CVyRI' => 'Vinculación y Relaciones',
    'UEPS' => 'Procedimientos Especiales',
    'SG' => 'Secretaría General'
];

$activitiesByArea = [];
foreach($labels as $key => $desc) {
    list($area, $year, $actId) = explode('-', $key);
    $num = str_replace('A', '', $actId);
    $activitiesByArea[$area][] = ['numero' => $num, 'descripcion' => $desc];
}

foreach($activitiesByArea as $areaCode => $acts) {
    if(!isset($urgMap[$areaCode])) continue;

    $urg = DB::connection('poa_prod')->table('unidades_responsables_gastos as urg')
        ->join('ejercicios as ej', 'urg.ejercicio_id', '=', 'ej.ejercicio_id')
        ->where('urg.nombre', 'LIKE', '%' . $urgMap[$areaCode] . '%')
        ->where('ej.ejercicio', 2027)
        ->select('urg.*')
        ->first();

    if(!$urg) {
        echo "No se encontró URG para $areaCode (" . $urgMap[$areaCode] . ")\n";
        continue;
    }

    $proyecto = DB::connection('poa_prod')->table('proyectos as p')
        ->join('responsables_operativos as ro', 'p.responsable_operativo_id', '=', 'ro.responsable_operativo_id')
        ->where('ro.unidad_responsable_gasto_id', $urg->unidad_responsable_gasto_id)
        ->select('p.*')
        ->first();

    if(!$proyecto) {
        echo "No se encontró Proyecto 2027 para $areaCode\n";
        continue;
    }

    // Borrar vieja tabla pivote (fallback if exists)
    $actIds = DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->pluck('id');
    if(count($actIds) > 0) {
        DB::connection('poa_prod')->table('actividad_riesgo')->whereIn('actividad_sustantiva_id', $actIds)->delete();
    }

    DB::connection('poa_prod')->table('actividades_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->delete();
    DB::connection('poa_prod')->table('acciones_sustantivas')->where('proyecto_id', $proyecto->proyecto_id)->delete();

    foreach($acts as $act) {
        DB::connection('poa_prod')->table('actividades_sustantivas')->insert([
            'proyecto_id' => $proyecto->proyecto_id,
            'numero' => $act['numero'],
            'descripcion' => $act['descripcion'],
            'recursos_asociados' => '',
            'es_resumida' => 1
        ]);
        
        DB::connection('poa_prod')->table('acciones_sustantivas')->insert([
            'proyecto_id' => $proyecto->proyecto_id,
            'numero' => $act['numero'],
            'descripcion' => $act['descripcion'],
            'recursos_asociados' => '',
            'es_resumida' => 1
        ]);
    }
    echo "Actualizado $areaCode con " . count($acts) . " actividades resumidas.\n";
}
echo "Done.\n";
