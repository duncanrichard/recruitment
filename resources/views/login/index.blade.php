<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="Masuk ke Sirekrut untuk mengelola dan memantau proses recruitment perusahaan.">
    <meta name="theme-color" content="#312e81">

    <title>Masuk | Sirekrut - Sistem Recruitment</title>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="antialiased">
    <div id="root"></div>
</body>
</html>
