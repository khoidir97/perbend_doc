<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginBasic extends Controller
{
  public function index()
  {
    if (Auth::check()) {
      return redirect()->intended('/');
    }

    return view('content.authentications.auth-login-basic');
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'name' => ['required', 'string'],
      'password' => ['required', 'string'],
    ], [
      'name.required' => 'Nama pengguna wajib diisi.',
      'password.required' => 'Password wajib diisi.',
    ]);

    $loginInput = trim($credentials['name']);
    $password = $credentials['password'];
    $remember = $request->boolean('remember');

    // Cari user berdasarkan 'name' (atau 'email' jika diinput email)
    $user = User::where('name', $loginInput)
      ->orWhere('email', $loginInput)
      ->first();

    if ($user) {
      // Cek password cocok (baik via Hash::check atau plain text jika baru diinput langsung di database)
      if (Hash::check($password, $user->password) || $user->password === $password) {
        // Jika password di database berupa plain text, perbarui menjadi hash aman
        if ($user->password === $password) {
          $user->password = Hash::make($password);
          $user->save();
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        return redirect()->intended('/');
      }
    }

    return back()->withErrors([
      'name' => 'Nama pengguna atau password yang Anda masukkan salah.',
    ])->onlyInput('name');
  }

  public function logout(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
  }
}
