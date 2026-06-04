<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class MigratedPasswordController extends Controller
{
    public function showSetupForm(Request $request)
    {
        // Proteksi: Hanya bisa diakses saat dilempar dari form login dengan sesi 'migrated_email'
        if (!$request->session()->has('migrated_email')) {
            return redirect('/');
        }

        return view('auth.migrate-password-setup', [
            'email' => $request->session()->get('migrated_email')
        ]);
    }

    public function storeNewPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // Double Protection: Pastikan hanya user migrasi yang bisa ke fungsi ini
        if (!$user->is_migrated) {
            return redirect('/')->with('error', 'Sistem mendeteksi akun ini telah diperbarui sebelumnya.');
        }

        // a & b. Enkripsi dan simpan password baru
        $user->password = Hash::make($request->password);
        
        // c. Cabut status migrasi 
        $user->is_migrated = false; 
        $user->save();

        // d. Otomatis login-kan ke dashboard
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Berhasil! Password baru Anda telah kami amankan.');
    }
}
