<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function register(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => trim($request->fname . ' ' . $request->lname),
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->back();
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->is_migrated) {
            return redirect()->route('password.migrate.setup')
                ->with('migrated_email', $user->email)
                ->with('info', 'Selamat datang di ekosistem baru ResearchAvenir! Harap perbarui password Anda demi keamanan dan kenyamanan akses.');
        }

        if (Auth::attempt($request->only('email', 'password'), true)) {
            $request->session()->regenerate();
            return redirect()->back();
        }

        return redirect()->back()->withErrors([
            'message' => 'Email atau password salah.'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->back();
    }
}
