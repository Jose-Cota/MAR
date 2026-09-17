<table>
    <thead>
        <tr>
            <th colspan="16" style="text-align: center; font-size: 16px; font-weight: bold;">
                Avance Trimestral y Acumulado (Ejercicio {{ $ejercicio }})
            </th>
        </tr>
        <tr>
            <th style="width: 15px;">URG</th>
            <th style="width: 15px;">PG</th>
            <th style="width: 15px;">SP</th>
            <th style="width: 15px;">PY</th>
            <th style="width: 40px;">Descripción de la Meta</th>
            @foreach($meses as $mes)
                <th style="width: 12px;">{{ $mes }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($datos as $urg)
            <tr>
                <td>{{ $urg->numero ?? '' }}</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>No hay conexión con BD para este volcado en este instante.</td>
                @foreach($meses as $mes)
                    <td>0%</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="17">Sin datos o sin conexión a base de datos.</td>
            </tr>
        @endforelse
    </tbody>
</table>
