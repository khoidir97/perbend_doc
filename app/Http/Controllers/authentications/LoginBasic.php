<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Pegawai;
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
      'pgw_nip' => ['required', 'string'],
      'pgw_password' => ['required', 'string'],
    ], [
      'pgw_nip.required' => 'NIP wajib diisi.',
      'pgw_password.required' => 'Password wajib diisi.',
    ]);

    $loginInput = trim($credentials['pgw_nip']);
    $password = $credentials['pgw_password'];
    $remember = $request->boolean('remember');

    // Cari pegawai aktif berdasarkan NIP
    $user = Pegawai::where('pgw_nip', $loginInput)
      ->whereNull('pgw_deleted_at')
      ->first();

    if ($user) {
      // Cek password cocok (baik via Hash::check atau plain text jika baru diinput langsung di database)
      if (Hash::check($password, $user->pgw_password) || $user->pgw_password === $password) {
        // Jika password di database berupa plain text, perbarui menjadi hash aman
        if ($user->pgw_password === $password) {
          $user->pgw_password = Hash::make($password);
          $user->save();
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        return redirect()->intended('/');
      }
    }

    return back()->withErrors([
      'pgw_nip' => 'NIP atau password yang Anda masukkan salah.',
    ])->onlyInput('pgw_nip');
  }

  public function changePassword(Request $request)
  {
    $request->validate([
      'current_password' => ['required', 'string'],
      'new_password' => ['required', 'string', 'min:6', 'confirmed'],
    ], [
      'current_password.required' => 'Password saat ini wajib diisi.',
      'new_password.required' => 'Password baru wajib diisi.',
      'new_password.min' => 'Password baru minimal 6 karakter.',
      'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
    ]);

    $user = Auth::user();

    // Cek kecocokan password saat ini (mendukung bcrypt hash maupun plain text)
    if (!Hash::check($request->current_password, $user->pgw_password) && $user->pgw_password !== $request->current_password) {
      return response()->json([
        'success' => false,
        'message' => 'Password saat ini yang Anda masukkan salah.',
        'errors' => [
          'current_password' => ['Password saat ini yang Anda masukkan salah.']
        ]
      ], 422);
    }

    $user->pgw_password = Hash::make($request->new_password);
    $user->save();

    return response()->json([
      'success' => true,
      'message' => 'Password Anda berhasil diperbarui!',
    ]);
  }

  public function logout(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
  }
}
