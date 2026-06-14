{{-- resources/views/client/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ \App\Models\Setting::get('company_name', 'SmartISP') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .login-wrapper { width: 100%; max-width: 400px; padding: 1rem; }
        .login-card {
            background: #fff; border-radius: 16px;
            padding: 2.5rem 2rem; box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }
        .brand { text-align: center; margin-bottom: 2rem; }
        .brand-logo {
            width: 60px; height: 60px; background: #00c897;
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px; font-size: 26px; font-weight: 800; color: #fff;
        }
        .brand h1 { font-size: 20px; font-weight: 700; color: #1a1f36; }
        .brand p  { font-size: 13px; color: #888; margin-top: 4px; }

        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .4px; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 14px; }
        .form-control {
            width: 100%; padding: 10px 12px 10px 38px;
            border: 1.5px solid #e0e4ef; border-radius: 8px;
            font-size: 13px; color: #333; outline: none; transition: border-color .2s;
        }
        .form-control:focus { border-color: #00c897; }
        .form-control.is-invalid { border-color: #e74c3c; }
        .invalid-feedback { font-size: 11px; color: #e74c3c; margin-top: 4px; }

        .remember-row { display: flex; align-items: center; gap: 8px; margin-bottom: 1.25rem; font-size: 13px; color: #666; }

        .btn-login {
            width: 100%; padding: 12px;
            background: #00c897; color: #fff; border: none;
            border-radius: 8px; font-size: 15px; font-weight: 600;
            cursor: pointer; transition: background .2s;
        }
        .btn-login:hover { background: #00b386; }

        .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 1rem; }
        .alert-danger  { background: #fff0f0; color: #c0392b; border: 1px solid #ffd0d0; }
        .alert-success { background: #f0fff8; color: #1a7a50; border: 1px solid #b0e8d0; }

        .hint-box {
            margin-top: 1.5rem; background: #f0faf7;
            border: 1px solid #b0e8d0; border-radius: 8px;
            padding: 12px 14px; font-size: 12px; color: #1a7a50;
        }
        .hint-box strong { display: block; margin-bottom: 4px; }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">

        <div class="brand">
            <div class="brand-logo">{{ strtoupper(substr(\App\Models\Setting::get('company_name', 'S'), 0, 1)) }}</div>
            <h1>{{ \App\Models\Setting::get('company_name', 'SmartISP') }}</h1>
            <p>Client Self-Service Portal</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('client.login') }}">
            @csrf

            <div class="form-group">
                <label>PPPoE Username</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" name="pppoe_username"
                        class="form-control {{ $errors->has('pppoe_username') ? 'is-invalid' : '' }}"
                        placeholder="Your PPPoE username"
                        value="{{ old('pppoe_username') }}"
                        autocomplete="username" required>
                </div>
                @error('pppoe_username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="Your PPPoE password"
                        autocomplete="current-password" required>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember" value="1">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt" style="margin-right:6px;"></i> Login
            </button>
        </form>

        <div class="hint-box">
            <strong><i class="fas fa-info-circle"></i> Login Info</strong>
            Username: Your PPPoE username<br>
            Password: Your PPPoE password
        </div>
    </div>
</div>
</body>
</html>
