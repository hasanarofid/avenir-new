<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Periksa apakah email sudah ada
            $existingUser = User::where('email', $googleUser->email)->first();
            
            if ($existingUser) {
                // Jika sudah ada, update google_id jika kosong
                if (!$existingUser->google_id) {
                    $existingUser->update(['google_id' => $googleUser->id]);
                }
                
                // Redirect ke 2FA Challenge (tidak langsung login)
                request()->session()->put('2fa:login:id', $existingUser->id);
                request()->session()->put('2fa:login:remember', true); // Google login usually remembered
                
                return redirect()->route('2fa.challenge');
            } else {
                // Jika belum ada, buat user baru
                $google2fa = new \PragmaRX\Google2FA\Google2FA();
                $secret = $google2fa->generateSecretKey();
                
                $recoveryCodes = [];
                for ($i = 0; $i < 8; $i++) {
                    $recoveryCodes[] = Str::random(10);
                }
                
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(Str::random(16)), // Password acak
                    'email_verified_at' => now(), // Anggap terverifikasi karena dari Google
                    'google2fa_secret' => $secret,
                    'recovery_codes' => $recoveryCodes,
                ]);
                
                // Redirect ke 2FA Setup (tidak langsung login)
                request()->session()->put('2fa:user:id', $user->id);
                
                return redirect()->route('2fa.setup');
            }

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Gagal login menggunakan Google.');
        }
    }
}
