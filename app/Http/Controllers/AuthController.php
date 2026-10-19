<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController {
 public function loginForm(){return view('auth.login');}
 public function login(Request $request){
  $data=$request->validate(['username'=>['required','string'],'password'=>['required','string']]);
  if(!Auth::attempt(['username'=>$data['username'],'password'=>$data['password'],'active'=>true]))return back()->withErrors(['username'=>'Invalid credentials or inactive account.'])->onlyInput('username');
  $request->session()->regenerate();
  return redirect()->intended(route('dashboard'));
 }
 public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('login');}
}
