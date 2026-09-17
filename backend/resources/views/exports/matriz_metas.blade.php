<table>
    <thead>
        <tr>
            <th colspan="16" style="text-align: center; font-size: 14px; font-weight: bold;">
                Matriz de Metas
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">URG</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Programa</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Subprograma</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Proyecto</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Meta</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Unidad de Medida</th>
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
        @foreach($proyectos as $row)
        <tr>
            <td>{{ $row['urg'] }}</td>
            <td>{{ $row['programa'] }}</td>
            <td>{{ $row['subprograma'] }}</td>
            <td>{{ $row['proyecto'] }}</td>
            <td>{{ $row['meta'] }}</td>
            <td>{{ $row['unidad_medida'] }}</td>
            @for($i = 1; $i <= 12; $i++)
                <td>{{ $row['meses'][$i]['programado'] ?? 0 }} / {{ $row['meses'][$i]['alcanzado'] ?? 0 }}</td>
            @endfor
        </tr>
        @endforeach
    </tbody>
</table>
