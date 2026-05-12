<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Pendaftaran' }}</title>

    @viteReactRefresh
    @vite([
        'resources/css/app.css',
        'resources/js/pages/pendaftaran/Index.jsx'
    ])
</head>
<body>
    <div id="pendaftaran-root"></div>
</body>
</html>