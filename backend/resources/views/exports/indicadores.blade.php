<table>
    <thead>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 14px; font-weight: bold;">
                Reporte de Indicadores
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">URG</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Proyecto</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Indicador</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Definición</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Método de Cálculo</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Frecuencia</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Unidad de Medida</th>
            <th style="font-weight: bold; background-color: #244062; color: #ffffff;">Meta Absoluta</th>
        </tr>
    </thead>
    <tbody>
        @foreach($indicadores as $ind)
        <tr>
            <td>{{ $ind->urg_numero }} - {{ $ind->urg_nombre }}</td>
            <td>{{ $ind->proyecto_numero }} - {{ $ind->proyecto_nombre }}</td>
            <td>{{ $ind->nombre }}</td>
            <td>{{ $ind->definicion }}</td>
            <td>{{ $ind->metodo_calculo }}</td>
            <td>{{ $ind->frecuencia }}</td>
            <td>{{ $ind->unidad_medida }}</td>
            <td>{{ $ind->meta }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
