<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Recursos Asociados</title>
    <style>
        @page { margin: 1cm 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        table.header { width: 100%; border: none; margin-bottom: 20px; }
        .header td { border: none; padding: 0; vertical-align: middle; }
        .title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 5px; }
        .subtitle { font-size: 12px; font-weight: bold; text-align: center; color: #555; }
        .ur-title { font-size: 12px; font-weight: bold; margin-top: 25px; margin-bottom: 10px; color: #1F4E79; border-bottom: 2px solid #1F4E79; padding-bottom: 5px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table th, .data-table td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        .data-table th { background-color: #f5f5f5; font-weight: bold; font-size: 11px; }
        ul.recursos-list { margin: 0; padding-left: 15px; }
        ul.recursos-list li { margin-bottom: 3px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 20%;">
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="Logo TECDMX" style="max-height: 55px;">
                @endif
            </td>
            <td style="width: 80%; text-align: center;">
                <div class="title">TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</div>
                <div class="subtitle">Reporte de Recursos Asociados - POA 2027</div>
            </td>
        </tr>
    </table>

    @foreach($datosAgrupados as $ur => $proyectos)
        <div class="ur-title">{{ $ur }}</div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Proyecto</th>
                    <th style="width: 40%;">Actividad Sustantiva</th>
                    <th style="width: 35%;">Recursos Asociados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proyectos as $proy)
                    @php $rowspan = count($proy['recursos']); @endphp
                    @foreach($proy['recursos'] as $index => $item)
                    <tr>
                        @if($index === 0)
                        <td rowspan="{{ $rowspan }}">
                            <strong>{{ $proy['numero'] }}</strong><br>
                            <span style="font-size: 9px; color: #555;">{{ $proy['nombre'] }}</span>
                        </td>
                        @endif
                        <td>{{ $item['actividad'] }}</td>
                        <td>{{ $item['recurso'] }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
