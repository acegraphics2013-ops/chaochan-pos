<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>Login — ChaoChan POS</title>
<link rel="icon" href="{{ asset('images/chaochan-logo.png') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="login-page">
<div class="login-shell">
 <section class="login-art">
  <img class="login-logo" src="{{ asset('images/chaochan-logo.png') }}" alt="ChaoChan Fried Rice">
 </section>
 <section class="login-panel">
  <div class="login-inner">
   <h1>Sign in to your account</h1>
   <p class="muted">Manage orders, kitchen, sales and more.</p>
   <div class="short-line"></div>
   @if($errors->any())<div class="flash flash-bad">{{ $errors->first() }}</div>@endif
   <form method="post" action="{{ route('login.submit') }}" class="form-stack">@csrf
    <label>Username<input name="username" required autocomplete="username" value="{{ old('username') }}" placeholder="Enter your username"></label>
    <label>Password<input type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"></label>
    <button class="btn btn-primary full" type="submit">Sign In →</button>
   </form>
   <div class="login-credit">
    <p>© {{ date('Y') }} ChaoChan Fried Rice. All rights reserved.</p>
    <p>Developed &amp; maintained by <a href="https://acecreatives.vercel.app" target="_blank" rel="noopener noreferrer">Ace Creatives</a></p>
   </div>
  </div>
 </section>
</div>
</body>
</html>
