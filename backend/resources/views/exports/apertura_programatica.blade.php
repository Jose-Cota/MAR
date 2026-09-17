<table>
    <thead>
        <tr>
            <th colspan="20" style="text-align: center; font-size: 14px; font-weight: bold;">
                PROGRAMA OPERATIVO ANUAL {{ $ejercicio }}
            </th>
        </tr>
        <tr>
            <th colspan="20" style="text-align: center; font-size: 14px; font-weight: bold;">
                PROYECTOS
            </th>
        </tr>
        <tr>
            <th rowspan="2" style="width: 8px; vertical-align: middle;">URG</th>
            <th rowspan="2" style="width: 8px; vertical-align: middle;">RO</th>
            <th rowspan="2" style="width: 8px; vertical-align: middle;">PG</th>
            <th rowspan="2" style="width: 8px; vertical-align: middle;">SP</th>
            <th rowspan="2" style="width: 8px; vertical-align: middle;">PY</th>
            <th rowspan="2" style="width: 50px; vertical-align: middle;">Denominación</th>
            <th rowspan="2" style="width: 20px; vertical-align: middle;">Unidad de medida</th>
            <th colspan="12" style="text-align: center;">Meses</th>
            <th rowspan="2" style="width: 12px; vertical-align: middle;">Total</th>
        </tr>
        <tr>
            @foreach($meses as $mes)
                <th style="width: 8px;">{{ $mes }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($datos as $row)
            <tr>
                <td style="text-align: center;">{{ $row->urg }}</td>
                <td style="text-align: center;">{{ $row->ro }}</td>
                <td style="text-align: center;">{{ $row->pg }}</td>
                <td style="text-align: center;">{{ $row->sp }}</td>
                <td style="text-align: center;">{{ $row->py }}</td>
                <td>{{ $row->denominacion }}</td>
                <td style="text-align: center;">{{ $row->unidad_medida }}</td>
                @foreach($row->meses as $val)
                    <td style="text-align: center;">{{ $val }}</td>
                @endforeach
                <td style="text-align: center; font-weight: bold;">{{ $row->total }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
