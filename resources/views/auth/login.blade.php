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
    <p>Developed &amp; maintained by <a href="https://acecreatives.vercel.app" target="_blank" rel="noopener noreferrer" id="ace-link">Ace Creatives</a></p>
   </div>
  </div>
 </section>
</div>
<div id="ace-confirm" class="modal-screen hidden" role="dialog" aria-modal="true" aria-labelledby="ace-confirm-title">
 <div class="modal-card">
  <h2 id="ace-confirm-title">Leave ChaoChan POS?</h2>
  <p class="muted">You are about to open the <strong>Ace Creatives</strong> website in a new tab.</p>
  <div class="row-actions">
   <button type="button" class="btn btn-primary" id="ace-yes">Yes, open link</button>
   <button type="button" class="btn btn-soft" id="ace-no">No, stay here</button>
  </div>
 </div>
</div>
<script>
 (function(){
  var link=document.getElementById('ace-link'),modal=document.getElementById('ace-confirm');
  if(!link||!modal)return;
  var url=link.getAttribute('href');
  link.addEventListener('click',function(e){e.preventDefault();modal.classList.remove('hidden');});
  document.getElementById('ace-no').addEventListener('click',function(){modal.classList.add('hidden');});
  document.getElementById('ace-yes').addEventListener('click',function(){modal.classList.add('hidden');window.open(url,'_blank','noopener,noreferrer');});
  modal.addEventListener('click',function(e){if(e.target===modal)modal.classList.add('hidden');});
 })();
</script>
</body>
</html>
