<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Código Rapidito')</title>

    <!-- ===== Bootstrap ===== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ===== Font Awesome (para íconos) ===== -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ===== Favicon opcional ===== -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo2.png') }}">

    <!-- ===== Color para navegador móvil ===== -->
    <meta name="theme-color" content="#0A1F44">
</head>
<body>

@yield('content')

<!-- ===== Bootstrap JS ===== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
