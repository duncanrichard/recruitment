<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF untuk request PATCH/POST/PUT/DELETE dari React --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Pendaftaran' }}</title>

    @viteReactRefresh
    @vite([
        'resources/css/app.css',
        'resources/js/pages/pendaftaran/Index.jsx'
    ])
</head>
<body>
    <div
        id="pendaftaran-root"
        data-token="{{ $token }}"
        data-pelamar='@json($pelamar)'
    ></div>
</body>
</html>
