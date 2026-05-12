<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>

    @viteReactRefresh
    @vite(['resources/js/pages/admin/index.jsx'])
</head>
<body>
    <div id="admin-root"></div>
</body>
</html>