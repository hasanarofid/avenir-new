<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Password;

class Verify2FAController extends Controller
{
    public function setup(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        
        $google2fa = new Google2FA();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            )
        );
        
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return Inertia::render('Auth/Setup2FA', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->google2fa_secret,
            'recoveryCodes' => $user->recovery_codes,
        ]);
    }

    public function confirmSetup(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);

        if ($valid) {
            $request->session()->forget('2fa:user:id');
            Auth::login($user);
            return redirect()->intended('/');
        }

        return back()->withErrors(['one_time_password' => 'Kode Autentikator tidak valid.']);
    }

    public function verify(Request $request)
    {
        $email = $request->session()->get('2fa:reset:email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        return Inertia::render('Auth/Verify2FA');
    }

    public function confirmVerify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $email = $request->session()->get('2fa:reset:email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->firstOrFail();
        $code = trim($request->code);
        
        $isValid = false;
        
        // Cek apakah itu 6 digit TOTP
        if (strlen($code) === 6 && is_numeric($code)) {
            $google2fa = new Google2FA();
            $isValid = $google2fa->verifyKey($user->google2fa_secret, $code);
        } else {
            // Cek apakah itu recovery code
            $recoveryCodes = $user->recovery_codes ?? [];
            if (in_array($code, $recoveryCodes)) {
                $isValid = true;
                // Hapus recovery code yang sudah dipakai
                $recoveryCodes = array_diff($recoveryCodes, [$code]);
                $user->recovery_codes = array_values($recoveryCodes);
                $user->save();
            }
        }

        if ($isValid) {
            $request->session()->forget('2fa:reset:email');
            
            // Generate token untuk user ini
            $token = Password::getRepository()->create($user);
            
            return redirect()->route('password.reset', ['token' => $token, 'email' => $user->email]);
        }

        return back()->withErrors(['code' => 'Kode Autentikator atau Recovery Code tidak valid.']);
    }

    public function challenge(Request $request)
    {
        $userId = $request->session()->get('2fa:login:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        
        // If legacy user without 2FA, just log them in
        if (!$user->google2fa_secret) {
            $remember = $request->session()->get('2fa:login:remember', false);
            $request->session()->forget(['2fa:login:id', '2fa:login:remember']);
            Auth::login($user, $remember);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return Inertia::render('Auth/Challenge2FA');
    }

    public function confirmChallenge(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = $request->session()->get('2fa:login:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $code = trim($request->code);
        
        $isValid = false;
        
        if (strlen($code) === 6 && is_numeric($code)) {
            $google2fa = new Google2FA();
            $isValid = $google2fa->verifyKey($user->google2fa_secret, $code);
        } else {
            $recoveryCodes = $user->recovery_codes ?? [];
            if (in_array($code, $recoveryCodes)) {
                $isValid = true;
                $recoveryCodes = array_diff($recoveryCodes, [$code]);
                $user->recovery_codes = array_values($recoveryCodes);
                $user->save();
            }
        }

        if ($isValid) {
            $remember = $request->session()->get('2fa:login:remember', false);
            $request->session()->forget(['2fa:login:id', '2fa:login:remember']);
            Auth::login($user, $remember);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return back()->withErrors(['code' => 'Kode Autentikator atau Recovery Code tidak valid.']);
    }
}
