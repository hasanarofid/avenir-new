<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $hasActivePremium = $user->hasActivePremium() || $user->hasRole('admin');
        $subscriptionEndsAt = null;
        $activePackage = null;

        if ($hasActivePremium) {
            $profile = \Illuminate\Support\Facades\DB::table('user_profiles')->where('user_id', $user->id)->first();
            $subscriptionEndsAt = $profile ? $profile->subscription_ends_at : null;
            
            $latestPayment = \Illuminate\Support\Facades\DB::table('payment_submissions')
                ->where('user_id', $user->id)
                ->where('status', 'verified')
                ->orderBy('verified_at', 'desc')
                ->first();
                
            if ($latestPayment) {
                $activePackage = $latestPayment->paket;
                // Translate package name
                if ($activePackage === '1bulan') $activePackage = 'Langganan 1 Bulan';
                elseif ($activePackage === '3bulan') $activePackage = 'Langganan 3 Bulan';
                elseif ($activePackage === '6bulan') $activePackage = 'Langganan 6 Bulan';
                elseif ($activePackage === '12bulan') $activePackage = 'Langganan 1 Tahun';
            } else {
                $activePackage = $user->hasRole('admin') ? 'Akses Administrator' : 'Akses Premium Khusus';
            }
            
            if ($subscriptionEndsAt) {
                $subscriptionEndsAt = \Carbon\Carbon::parse($subscriptionEndsAt)->translatedFormat('d F Y');
            } else if (!$user->hasRole('admin')) {
                $subscriptionEndsAt = 'Akses Seumur Hidup';
            }
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'hasActivePremium' => $hasActivePremium,
            'subscriptionEndsAt' => $subscriptionEndsAt,
            'activePackage' => $activePackage,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('photo')) {
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->hasRole('admin')) {
            return Redirect::back()->with('error', 'Akun administrator tidak dapat dihapus.');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
