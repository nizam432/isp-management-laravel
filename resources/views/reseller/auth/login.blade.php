<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            min-height: 100vh; display:flex; align-items:center; justify-content:center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            font-family:-apple-system,Segoe UI,Roboto,sans-serif;
        }
        .login-card {
            background:#fff; border-radius:16px; padding:36px 32px; width:100%; max-width:380px;
            box-shadow: 0 20px 50px rgba(0,0,0,.3);
        }
        .login-icon {
            width:60px; height:60px; border-radius:50%; background:#f0fdf4;
            display:flex; align-items:center; justify-content:center; margin: 0 auto 16px;
        }
        .login-icon i { font-size:1.6rem; color:#28a745; }
        .login-title { text-align:center; font-weight:700; color:#1a1a2e; margin-bottom:4px; }
        .login-sub { text-align:center; color:#9ca3af; font-size:.85rem; margin-bottom:24px; }
        .form-control { border-radius:8px; padding:10px 14px; font-size:.9rem; }
        .btn-login {
            background: linear-gradient(135deg, #28a745, #20c997); border:none; color:#fff;
            font-weight:600; padding:10px; border-radius:8px; width:100%;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-icon"><i class="fas fa-network-wired"></i></div>
        <h5 class="login-title">Reseller Portal</h5>
        <p class="login-sub">Sign in to manage your clients</p>

        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger py-2" style="font-size:.85rem">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reseller.login.submit') }}">
            @csrf
            <div class="form-group">
                <label class="small font-weight-bold">Username</label>
                <input type="text" name="username" class="form-control" required autofocus value="{{ old('username') }}">
            </div>
            <div class="form-group">
                <label class="small font-weight-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt mr-1"></i> Sign In
            </button>
        </form>
    </div>
</body>
</html>
