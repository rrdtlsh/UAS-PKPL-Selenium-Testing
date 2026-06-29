<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrator | Lab Monitoring</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: #fff; padding: 40px 48px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.08); text-align: center; max-width: 480px; width: 100%; }
        .icon { font-size: 48px; margin-bottom: 16px; }
        h1 { font-size: 22px; font-weight: 600; color: #1a202c; margin-bottom: 8px; }
        p { color: #718096; font-size: 14px; line-height: 1.6; margin-bottom: 24px; }
        .badge { display: inline-block; background: #ebf8ff; color: #2b6cb0; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; margin-bottom: 24px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🛡️</div>
        <div class="badge">Administrator</div>
        <h1>Panel Administrator</h1>
        <p>
            Selamat datang, <strong>{{ auth()->user()->name }}</strong>.<br>
            Halaman ini hanya dapat diakses oleh Administrator.
        </p>
        <a href="{{ route('monitoring') }}" class="btn">Kembali ke Monitoring</a>
    </div>
</body>
</html>
