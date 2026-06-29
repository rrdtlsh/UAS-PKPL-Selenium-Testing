<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | Sistem Monitoring & Alert</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            background:#f4f6f9;
        }

        .login-card{
            max-width:420px;
            margin:auto;
            margin-top:80px;
            border:none;
            border-radius:15px;
            box-shadow:0 5px 20px rgba(0,0,0,.1);
        }

        .card-header{
            background:#0d6efd;
            color:white;
            text-align:center;
            font-weight:bold;
            font-size:20px;
            padding:20px;
            border-radius:15px 15px 0 0;
        }

        .logo{
            font-size:50px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card login-card">

        <div class="card-header">

            <div class="logo">
                🧪
            </div>

            Sistem Monitoring & Alert
            <br>
            Kondisi Penyimpanan Obat

        </div>

        <div class="card-body p-4">

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}">

                @csrf

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Masukkan email"
                        value="{{ old('email') }}"
                        required>

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        required>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100">

                    Login

                </button>

            </form>

        </div>

        <div class="card-footer text-center text-muted">

            © 2026 Sistem Monitoring & Alert
            <br>
            Uji Stabilitas Obat

        </div>

    </div>

</div>

</body>

</html>