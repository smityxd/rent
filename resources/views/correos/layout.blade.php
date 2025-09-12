<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.2;
            margin: 40px;
            color: #333;
            font-size:12px
        }
        h1 {
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        p {
            text-align: justify;
            margin-bottom: 20px;
        }
        .firmas {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .firmas div {
            text-align: center;
            width: 45%;
        }
        .linea {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }
        .clausula{
            text-decoration:underline;
            font-weight: bold;
        }


        
        @page {
            margin: 100px 50px 80px 50px;
        }

        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 60px;
            text-align: right;
            font-size: 12px;
            color: #555;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            font-size: 12px;
            text-align: center;
            color: #555;
        }

        .page-number:after {
            content: "Página " counter(page);
        }

        .warning{
            background-color:#fff1da;
            color: #764800
        }
        .success{
            background-color:#95ffd1;
            color: #004c2b
        }
        .danger{
            background-color:#da542e;
            color: #fff
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .recibo th, .recibo td {
            border: 1px solid #ccc;
            padding: 10px; /* Adds 15px padding inside each cell */
            text-align: center;
        }
        .pendiente{
            background-color:#f7f7f7;
            font-weight:bold;
        }
    </style>
</head>
<body>


<footer>
    <div class="page-number"></div>
</footer>

<main>
    @yield('contenido')
</main>

</body>
</html>
