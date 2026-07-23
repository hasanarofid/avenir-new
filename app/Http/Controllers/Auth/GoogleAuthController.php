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
                // Update google_id jika belum terhubung
                if (!$existingUser->google_id) {
                    $existingUser->google_id = $googleUser->id;
                }
                // Otomatis verifikasi email karena login via Google
                if (!$existingUser->email_verified_at) {
                    $existingUser->email_verified_at = now();
                }
                $existingUser->save();

                Auth::login($existingUser, true);

                return redirect()->intended(route('dashboard', absolute: false));
            } else {
                // Buat user baru dengan status email terverifikasi
                $nameParts = explode(' ', trim($googleUser->name), 2);
                $fname = $nameParts[0];
                $lname = isset($nameParts[1]) ? $nameParts[1] : '';

                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(), // Terverifikasi via Google
                ]);

                // Assign default role
                $user->assignRole('user');

                // Create profile
                \Illuminate\Support\Facades\DB::table('user_profiles')->insert([
                    'user_id' => $user->id,
                    'first_name' => $fname,
                    'last_name' => $lname,
                    'is_subscriber' => false,
                    'subscription_ends_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Auth::login($user, true);

                return redirect()->intended(route('dashboard', absolute: false));
            }

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Gagal login menggunakan Google.');
        }
    }
}
