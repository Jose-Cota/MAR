<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Descriptiva - {{ $proyecto->py }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 14px;
            margin: 0 0 5px 0;
        }
        .header h2 {
            font-size: 12px;
            margin: 0;
            font-weight: normal;
        }
        .section-title {
            background-color: #e3f2fd;
            padding: 5px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 5px;
            border-bottom: 2px solid #2196f3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .meta-row { background-color: #f5f5f5; font-weight: bold; }
        .ind-row { font-size: 9px; }
        
        .fd-ref {
            display: inline-block;
            background: #2b3a4a;
            color: #fff;
            width: 14px;
            height: 14px;
            line-height: 14px;
            text-align: center;
            border-radius: 50%;
            font-size: 9px;
            font-weight: bold;
            margin-right: 4px;
        }
        .label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 2px;
        }
        .value {
            border: 1px solid #ccc;
            padding: 4px;
            background: #fff;
            min-height: 14px;
        }
    </style>
</head>
<body style="position: relative;">
    @if($proyecto->estatus !== 'Verificado')
        <div style="position: fixed; top: 40%; left: 0; width: 100%; text-align: center; font-size: 80px; color: rgba(255,0,0,0.15); transform: rotate(-45deg); z-index: 1000; pointer-events: none;">
            Vista Preliminar
        </div>
    @endif

    <div class="header">
        <table style="width: 100%; border: none; margin: 0; padding: 0;">
            <tr style="border: none;">
                <td style="width: 25%; text-align: left; vertical-align: middle; border: none; padding: 0;">
                    @if(!empty($logo))
                        <img src="{{ $logo }}" alt="Logo TECDMX" style="max-height: 60px;">
                    @endif
                </td>
                <td style="width: 75%; text-align: center; vertical-align: middle; border: none; padding: 0;">
                    <div style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</div>
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Programa Operativo Anual {{ $proyecto->ejercicio_anio ?? '2027' }}</div>
                    <div style="font-size: 12px;">Ficha Descriptiva del Proyecto</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1. Identificación de responsables -->
    <div class="section-title">Identificación de responsables</div>
    <table>
        <tr>
            <td width="50%" style="border: none; padding: 0 4px 4px 0;">
                <span class="label"><span class="fd-ref">1</span> Unidad Responsable (UR)</span>
                <div class="value">{{ $proyecto->urg ?? '' }} - {{ $proyecto->urg_nombre ?? '' }}</div>
            </td>
            <td width="50%" style="border: none; padding: 0 0 4px 4px;">
                <span class="label"><span class="fd-ref">2</span> Responsable operativo</span>
                <div class="value">{{ $proyecto->ro ?? '' }} - {{ $proyecto->responsable_operativo ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td width="50%" style="border: none; padding: 4px 4px 4px 0;">
                <span class="label"><span class="fd-ref">3</span> Responsable de la ficha</span>
                <div class="value">{{ $proyecto->responsable_ficha ?? 'Sin asignar' }}</div>
            </td>
            <td width="50%" style="border: none; padding: 4px 0 4px 4px;">
                <span class="label">Puesto</span>
                <div class="value">{{ $proyecto->puesto_responsable_ficha ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border: none; padding: 4px 0;">
                <span class="label"><span class="fd-ref">4</span> Objetivo de la UR</span>
                <div class="value">{{ $proyecto->objetivo ?: 'No capturado' }}</div>
            </td>
        </tr>
    </table>

    <!-- 2. Estructura programática -->
    <div class="section-title"><span class="fd-ref">5</span> Estructura programática</div>
    <div style="margin-bottom: 5px; background: #f9f9f9; padding: 4px; border: 1px solid #ccc;">
        <strong>Clave programática:</strong> <span style="font-size: 11px;">{{ $proyecto->urg }}.{{ $proyecto->ro }}.{{ $proyecto->pg }}.{{ $proyecto->sp }}.{{ $proyecto->py }}</span> — {{ $proyecto->proyecto_nombre }}
    </div>
    <table>
        <tr>
            <td width="50%" style="border: none; padding: 0 4px 4px 0;">
                <span class="label">UR</span>
                <div class="value">{{ $proyecto->urg ?? '' }} - {{ $proyecto->urg_nombre ?? '' }}</div>
            </td>
            <td width="50%" style="border: none; padding: 0 0 4px 4px;">
                <span class="label">RO</span>
                <div class="value">{{ $proyecto->ro ?? '' }} - {{ $proyecto->responsable_operativo ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 4px 4px 4px 0;">
                <span class="label">PG · Programa</span>
                <div class="value">{{ $proyecto->pg ?? '' }} - {{ $proyecto->programa_nombre ?? '' }}</div>
            </td>
            <td style="border: none; padding: 4px 0 4px 4px;">
                <span class="label">SP · Subprograma</span>
                <div class="value">{{ $proyecto->sp ?? '' }} - {{ $proyecto->subprograma_nombre ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border: none; padding: 4px 0 0 0;">
                <span class="label">PY · Proyecto</span>
                <div class="value">{{ $proyecto->py ?? '' }} - {{ $proyecto->proyecto_nombre ?? '' }}</div>
            </td>
        </tr>
    </table>

    <!-- 3. Datos del proyecto y alineación -->
    <div class="section-title">Datos del proyecto y alineación estratégica</div>
    <table>
        <tr>
            <td style="border: none; padding: 0 0 4px 0;">
                <span class="label"><span class="fd-ref">6</span> Proyecto</span>
                <div class="value">{{ $proyecto->proyecto_nombre ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 4px 0;">
                <span class="label"><span class="fd-ref">7</span> Descripción del proyecto</span>
                <div class="value">{{ $proyecto->descripcion ?? '' }}</div>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 4px 0;">
                <span class="label"><span class="fd-ref">8</span> Objetivo del proyecto</span>
                <div class="value">{{ $proyecto->justificacion ?? '' }}</div>
            </td>
        </tr>
        
        <!-- Alineación PEI -->
        @if(isset($pei['alineaciones']) && count($pei['alineaciones']) > 0)
            @php
                $agrupadas = [];
                foreach($pei['alineaciones'] as $al) {
                    $agrupadas[$al['linea_id']][] = $al['objetivo_id'];
                }
                $idx = 1;
            @endphp
            @foreach($agrupadas as $lineaId => $objetivoIds)
                @php
                    $lineaObj = null;
                    foreach($pei['lineas'] as $l) {
                        if ($l['id'] == $lineaId) {
                            $lineaObj = $l; break;
                        }
                    }
                @endphp
                <tr>
                    <td style="border: none; padding: 4px 0;">
                        <span class="label"><span class="fd-ref">9</span> Objetivo estratégico {{ count($agrupadas) > 1 ? "($idx)" : "" }}</span>
                        <div class="value">
                            @if($lineaObj)
                                {{ $lineaObj['numero'] }}. {{ $lineaObj['nombre'] }}
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 4px 0 0 0;">
                        <span class="label"><span class="fd-ref">10</span> Línea estratégica {{ count($agrupadas) > 1 ? "($idx)" : "" }}</span>
                        <div class="value">
                            @foreach($objetivoIds as $objId)
                                @php
                                    $objNombre = '';
                                    if($lineaObj && isset($lineaObj['objetivos'])) {
                                        foreach($lineaObj['objetivos'] as $o) {
                                            if ($o['id'] == $objId) {
                                                $objNombre = $o['nombre']; break;
                                            }
                                        }
                                    }
                                @endphp
                                @if($objNombre)
                                    <div>{{ $objNombre }}</div>
                                @endif
                            @endforeach
                        </div>
                    </td>
                </tr>
                @php $idx++; @endphp
            @endforeach
        @else
            <tr>
                <td style="border: none; padding: 4px 0;"><p>No cuenta con alineación PEI.</p></td>
            </tr>
        @endif
    </table>

    <!-- 4. Metas -->
    @php
        $principales = [];
        $complementarias = [];
        $sumaPorcentajes = 0;
        foreach($metas as $meta) {
            if ($meta['tipo'] == 'principal') {
                $principales[] = $meta;
            } else {
                $complementarias[] = $meta;
                $sumaPorcentajes += floatval($meta['peso'] ?? 0);
            }
        }
        $formattedSuma = rtrim(rtrim(number_format($sumaPorcentajes, 2, '.', ''), '0'), '.') . '%';
    @endphp

    @foreach($principales as $idx => $metaPrin)
    <div class="section-title">Cuantificación de metas · Meta principal {{ count($principales) > 1 ? ($idx+1) : '' }}</div>
    <table style="border: none;">
        <tr>
            <td colspan="2" style="border: none; padding: 0 0 8px 0;">
                <span class="label"><span class="fd-ref">11</span> Denominación de la meta principal</span>
                <div class="value">{{ $metaPrin['nombre'] }}</div>
            </td>
        </tr>
        <tr>
            <td width="80%" style="border: none; padding: 0 4px 0 0;">
                <span class="label"><span class="fd-ref">12</span> Programación mensual (absoluta)</span>
                <table style="margin:0;">
                    <tr>
                        @for($i=1; $i<=6; $i++)
                            <th style="text-align:center;">{{ substr(['Ene','Feb','Mar','Abr','May','Jun'][$i-1], 0, 3) }}</th>
                        @endfor
                    </tr>
                    <tr>
                        @for($i=1; $i<=6; $i++)
                            <td style="text-align:center; background:#fff;">{{ $formattedSuma }}</td>
                        @endfor
                    </tr>
                    <tr>
                        @for($i=7; $i<=12; $i++)
                            <th style="text-align:center;">{{ substr(['Jul','Ago','Sep','Oct','Nov','Dic'][$i-7], 0, 3) }}</th>
                        @endfor
                    </tr>
                    <tr>
                        @for($i=7; $i<=12; $i++)
                            <td style="text-align:center; background:#fff;">{{ $formattedSuma }}</td>
                        @endfor
                    </tr>
                </table>
            </td>
            <td width="20%" style="border: none; padding: 0 0 0 4px; vertical-align: top;">
                <span class="label"><span class="fd-ref">13</span> Total anual</span>
                <div class="value text-center" style="font-size:16px; padding:20px 0;">
                    {{ $formattedSuma }}
                </div>
            </td>
        </tr>
    </table>
    @endforeach

    @foreach($complementarias as $idx => $metaComp)
    <div class="section-title">Cuantificación de metas · Meta complementaria {{ $idx + 1 }}</div>
    <table style="border:none;">
        <tr>
            <td colspan="4" style="border:none; padding:0 0 4px 0;">
                <span class="label"><span class="fd-ref">15</span> Denominación de la meta complementaria {{ $idx + 1 }}</span>
                <div class="value">{{ $metaComp['nombre'] }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="border:none; padding:4px 0;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td width="30%" style="border:none; padding:0 4px 4px 0; vertical-align: top;">
                            <div style="margin-bottom: 12px;">
                                <span class="label"><span class="fd-ref">16</span> Unidad de medida</span>
                                <div class="value" style="font-size:9px;">{{ $metaComp['unidad_medida'] ?: 'Sin U.M.' }}</div>
                            </div>
                            <div>
                                <span class="label"><span class="fd-ref">17</span> Tipo</span>
                                <div class="value" style="font-size:9px; padding-top:2px;">{{ $metaComp['tmc'] == 1 ? 'Programable' : 'No Programable' }}</div>
                            </div>
                        </td>
                        <td width="50%" style="border:none; padding:0 4px 4px 0; vertical-align: top;">
                            <span class="label"><span class="fd-ref">18</span> Programación mensual</span>
                            <table style="margin:0; font-size:8px; width:100%;">
                                <tr>
                                    @for($i=1; $i<=6; $i++)
                                        <th style="text-align:center; padding:2px;">{{ substr(['Ene','Feb','Mar','Abr','May','Jun'][$i-1], 0, 3) }}</th>
                                    @endfor
                                </tr>
                                <tr>
                                    @for($i=1; $i<=6; $i++)
                                        <td style="text-align:center; padding:2px; background:#fff;">{{ $metaComp['tmc'] != 1 && empty($metaComp['meses'][$i]) ? 'NP' : ($metaComp['meses'][$i] ?? 0) }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    @for($i=7; $i<=12; $i++)
                                        <th style="text-align:center; padding:2px;">{{ substr(['Jul','Ago','Sep','Oct','Nov','Dic'][$i-7], 0, 3) }}</th>
                                    @endfor
                                </tr>
                                <tr>
                                    @for($i=7; $i<=12; $i++)
                                        <td style="text-align:center; padding:2px; background:#fff;">{{ $metaComp['tmc'] != 1 && empty($metaComp['meses'][$i]) ? 'NP' : ($metaComp['meses'][$i] ?? 0) }}</td>
                                    @endfor
                                </tr>
                            </table>
                        </td>
                        <td width="20%" style="border:none; padding:0 0 4px 10px; vertical-align: top;">
                            <div style="margin-bottom:12px;">
                                <span class="label"><span class="fd-ref">19</span> Total anual</span>
                                <div class="value text-center">{{ $metaComp['tmc'] != 1 ? 'NP' : ($metaComp['total_anual'] ?? 0) }}</div>
                            </div>
                            <div>
                                <span class="label"><span class="fd-ref">20</span> Valor (%)</span>
                                <div class="value text-center">{{ $metaComp['peso'] ?? '0' }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @endforeach

    <!-- 6. Indicadores -->
    <div class="section-title">Indicadores</div>
    @foreach($complementarias as $idx => $metaComp)
        @if(isset($metaComp['indicadores']) && count($metaComp['indicadores']) > 0)
            <div style="background:#f0f0f0; padding:4px; border:1px solid #ccc; margin-bottom:10px;">
                <span class="label" style="font-size:10px;">Indicador de la Meta Complementaria {{ $idx + 1 }}</span>
                @foreach($metaComp['indicadores'] as $indIndex => $ind)
                    <table style="margin-bottom:{{ $indIndex < count($metaComp['indicadores']) - 1 ? '15px' : '0' }}; background:#fff; border-bottom: {{ $indIndex < count($metaComp['indicadores']) - 1 ? '2px dashed #ccc' : 'none' }};">
                        <tr>
                            <th width="25%"><span class="fd-ref">21</span> Alineación:</th><td colspan="3">Meta {{ $idx + 1 }} - {{ $metaComp['nombre'] }}</td>
                        </tr>
                        <tr>
                            <th width="25%"><span class="fd-ref">22</span> Nombre:</th><td width="25%">{{ $ind->nombre }}</td>
                            <th width="25%"><span class="fd-ref">23</span> Objetivo:</th><td width="25%">{{ $ind->objetivo ?? $ind->definicion ?? '' }}</td>
                        </tr>
                        <tr>
                            <th><span class="fd-ref">24</span> Unidad de medida:</th><td>{{ $metaComp['unidad_medida'] ?: 'Sin U.M.' }}</td>
                            <th><span class="fd-ref">25</span> Método de cálculo:</th><td>{{ $metaComp['tmc'] != 1 ? 'Resultado = (Atendido/Recibido)*100' : 'Resultado = (Atendido/Programado)*100' }}</td>
                        </tr>
                        <tr>
                            <th><span class="fd-ref">26</span> Dimensión a medir:</th><td>{{ $ind->dimension ?? 'Eficacia' }}</td>
                            <th><span class="fd-ref">27</span> Frecuencia de medición:</th><td>Mensual</td>
                        </tr>
                    </table>
                @endforeach
            </div>
        @else
            <div style="background:#f0f0f0; padding:4px; border:1px solid #ccc; margin-bottom:10px;">
                <span class="label" style="font-size:10px;">Indicador de la Meta Complementaria {{ $idx + 1 }}</span>
                <div style="background:#fff; padding:8px; font-size:10px; color:#666;">No se ha definido el indicador para esta meta complementaria.</div>
            </div>
        @endif
    @endforeach

    <!-- 5. Actividades Sustantivas -->
    <div class="section-title">Actividades Sustantivas</div>
    @if(count($actividades) > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center"><span class="fd-ref">28</span></th>
                    <th width="50%">Descripción de las Actividades <span class="fd-ref">29</span></th>
                    <th width="45%">Recursos Asociados <span class="fd-ref">30</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($actividades as $idx => $act)
                    <tr>
                        <td class="text-center" style="background:#fff;">{{ $idx + 1 }}</td>
                        <td style="background:#fff;">{{ $act->descripcion }}</td>
                        <td style="background:#fff;">{{ $act->recursos_asociados ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se han capturado actividades sustantivas.</p>
    @endif
    
    <!-- Firmas -->
    @if($proyecto->estatus === 'Verificado')
        <div style="margin-top: 50px;">
            <table style="border:none;">
                <tr style="border:none;">
                    <td width="50%" class="text-center" style="border:none;">
                        <div style="border-bottom: 1px solid #000; margin: 0 20px 5px 20px; height: 50px;"></div>
                        <strong>Responsable de la Ficha</strong><br/>
                        {{ $proyecto->responsable_ficha ?? '' }}<br/>
                        <small>{{ $proyecto->puesto_responsable_ficha ?? '' }}</small>
                    </td>
                    <td width="50%" class="text-center" style="border:none;">
                        <div style="border-bottom: 1px solid #000; margin: 0 20px 5px 20px; height: 50px;"></div>
                        <strong>Autoriza</strong><br/>
                        {{ $proyecto->autorizante_nombre ?? '' }}<br/>
                        <small>{{ $proyecto->autorizante_puesto ?? '' }}</small>
                    </td>
                </tr>
            </table>
            @if(!empty($proyecto->fecha_verificacion))
                @php
                    \Carbon\Carbon::setLocale('es');
                    $fecha = \Carbon\Carbon::parse($proyecto->fecha_verificacion)->translatedFormat('d \d\e F \d\e Y');
                @endphp
                <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #555;">
                    {{ $fecha }}
                </div>
            @endif
        </div>
    @endif
</body>
</html>
