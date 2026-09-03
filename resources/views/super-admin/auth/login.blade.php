<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>Super Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 380px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
            overflow: hidden;
        }
        .login-card .card-header {
            background: #1e3c72;
            color: #fff;
            text-align: center;
            padding: 24px;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-user-shield"></i> Super Admin</h4>
        <small>ISP Management — Central Panel</small>
    </div>
    <div class="card-body p-4">

        @if($errors->any())
        <div class="alert alert-danger py-2">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form action="{{ route('super-admin.login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>ইমেইল</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label>পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">আমাকে মনে রাখো</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">লগইন করুন</button>
        </form>

    </div>
</div>

</body>
</html>
