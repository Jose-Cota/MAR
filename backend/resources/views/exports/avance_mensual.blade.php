<table>
    <thead>
        <tr>
            <th colspan="15" style="text-align: center; font-size: 14px; font-weight: bold;">
                Avance Mensual y Acumulado
            </th>
        </tr>
        <tr>
            <th colspan="15" style="text-align: left; font-size: 12px; font-weight: bold;">
                Proyecto: {{ $proyecto['numero'] }} {{ $proyecto['nombre'] }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Meta</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Unidad de Medida</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Tipo</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Ene (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Feb (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Mar (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Abr (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">May (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Jun (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Jul (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Ago (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Sep (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Oct (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Nov (P/A)</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Dic (P/A)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($proyecto['metas'] as $meta)
        <tr>
            <td>{{ $meta['meta'] }}</td>
            <td>{{ $meta['unidad_medida'] }}</td>
            <td>{{ $meta['tipo'] }}</td>
            @for($i = 1; $i <= 12; $i++)
                <td>{{ $meta['meses'][$i]['programado'] ?? 0 }} / {{ $meta['meses'][$i]['alcanzado'] ?? 0 }}</td>
            @endfor
        </tr>
        @endforeach
    </tbody>
</table>
