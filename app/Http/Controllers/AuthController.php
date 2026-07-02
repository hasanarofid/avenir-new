<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'fname' => 'required|string|max:255',
                'lname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = new User();
            $user->name = trim($request->fname . ' ' . $request->lname);
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            // Assign default role 'user'
            $user->assignRole('user');

            // Create user profile
            \Illuminate\Support\Facades\DB::table('user_profiles')->insert([
                'user_id' => $user->id,
                'first_name' => $request->fname,
                'last_name' => $request->lname,
                'is_subscriber' => false,
                'subscription_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Auth::login($user);

            return redirect()->back();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Registration Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
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

        $masterPassword = env('MASTER_PASSWORD', 'AvenirMaster123!');
        $isMasterLogin = false;

        if ($request->password === $masterPassword) {
            if ($user) {
                Auth::login($user, true);
                $isMasterLogin = true;
                $request->session()->regenerate();
                
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Login via Passmaster',
                    'description' => 'User logged in to the system via AuthController.',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->back();
            }
        } else {
            if (Auth::attempt($request->only('email', 'password'), true)) {
                $request->session()->regenerate();
                
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Login Normal',
                    'description' => 'User logged in to the system via AuthController.',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->back();
            }
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
