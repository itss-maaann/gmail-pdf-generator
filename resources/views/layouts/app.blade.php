<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name', 'Gmail PDF Generator') }}</title>
    <link rel="stylesheet" href="https://unpkg.com/sanitize.css">
    <style>
        body { font-family: Arial; max-width: 700px; margin: 40px auto; }
        .form-section { border: 1px solid #ccc; padding: 25px; margin-bottom: 30px; border-radius: 8px; }
        .btn-auth button, .btn-submit { padding: 12px; margin-top: 10px; width: 100%; }
        .btn-submit { background: #007BFF; color: white; border: none; cursor: pointer; }
        .btn-submit:hover { background: #0056b3; }
        .section-title { font-weight: bold; font-size: 20px; margin-bottom: 10px; }
        .note { font-size: 12px; color: #666; margin-top: -10px; margin-bottom: 15px; }
        .status-icon { width: 24px; display: inline-block; font-size: 18px; margin-left: 8px; }
        .input-group { display: flex; align-items: center; gap: 10px; }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
