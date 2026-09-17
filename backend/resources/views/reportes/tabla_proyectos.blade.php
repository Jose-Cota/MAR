<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tabla de Proyectos 2027</title>
    <style>
        @page {
            margin: 120px 30px 60px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        header {
            position: fixed;
            top: -100px;
            left: 0px;
            right: 0px;
            height: 80px;
            text-align: center;
            border-bottom: 2px solid #1F4E79;
            padding-bottom: 10px;
        }
        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 30px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .logo {
            position: absolute;
            left: 0;
            top: 0px;
            height: 65px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #1F4E79;
            color: #ffffff;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .col-ur { width: 4%; }
        .col-ro { width: 5%; }
        .col-pg { width: 4%; }
        .col-sp { width: 4%; }
        .col-py { width: 4%; }
        .col-nombre { width: 79%; }
        h1 {
            text-align: center;
            color: #143352;
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
        }
        h2 {
            text-align: center;
            color: #143352;
            margin: 2px 0 0 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <header>
        <img src="{{ public_path('logo_tecdmx.png') }}" class="logo" alt="Logo TECDMX">
        <h1>Tribunal Electoral de la Ciudad de México</h1>
        <h2>Tabla de Proyectos 2027</h2>
    </header>

    <footer>
        <script type="text/php">
            if ( isset($pdf) ) {
                $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                $size = 9;
                $font = $fontMetrics->get_font("Arial, sans-serif", "normal");
                $width = $fontMetrics->get_text_width($text, $font, $size);
                
                // Position text at right side (A4 width is approx 595pt, margins are 30pt left + 30pt right = 535pt available)
                // We'll just place it at X=520 so it fits on the right
                $x = 595 - 30 - $width;
                $y = $pdf->get_height() - 40; 
                
                $pdf->page_text($x, $y, $text, $font, $size, array(0.33, 0.33, 0.33));
            }
        </script>
    </footer>

    <main>
        <table>
            <thead>
                <tr>
                    <th class="col-ur">UR</th>
                    <th class="col-ro">RO</th>
                    <th class="col-pg">PG</th>
                    <th class="col-sp">SP</th>
                    <th class="col-py">PY</th>
                    <th class="col-nombre">Denominación Proyecto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proyectos as $p)
                    <tr>
                        <td class="text-center">{{ $p->ur }}</td>
                        <td class="text-center">{{ $p->ro }}</td>
                        <td class="text-center">{{ $p->pg }}</td>
                        <td class="text-center">{{ $p->sp }}</td>
                        <td class="text-center">{{ $p->py }}</td>
                        <td>{{ $p->denominacion_proyecto }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</body>
</html>
