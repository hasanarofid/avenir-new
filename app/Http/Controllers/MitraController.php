<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Research;
use App\Models\Article;
use App\Models\Partner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MitraController extends Controller
{
    public function dashboard()
    {
          /** @var \App\Models\User $user */
        $user = Auth::user()->load('partner');
        
        try {
            $researches = Research::where('author_id', $user->id)->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $researches = [];
        }
        
        try {
            $articles = Article::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $articles = [];
        }
        
        return Inertia::render('Mitra/Dashboard', [
            'researches' => $researches,
            'articles' => $articles,
        ]);
    }

    public function researches()
    {
        try {
            $researches = Research::where('author_id', Auth::id())->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $researches = [];
        }
        
        return Inertia::render('Mitra/Researches', [
            'researches' => $researches,
        ]);
    }

    public function profile()
    {
            /** @var \App\Models\User $user */
        $user = Auth::user()->load('partner');
        return Inertia::render('Mitra/Profile', [
            'user' => $user,
            'partner' => $user->partner,
        ]);
    }

    public function create()
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->partner) {
                if ($user->partner->is_verified) {
                    return redirect()->route('mitra.dashboard');
                }
                return redirect('/profile')->with('info', 'Anda sudah melakukan pengajuan pendaftaran mitra.');
            }
        }
        return Inertia::render('Mitra/Register');
    }

    public function store(Request $request)
    {
        if (Auth::check() && Auth::user()->partner) {
            return redirect('/profile')->with('info', 'Anda sudah melakukan pengajuan pendaftaran mitra.');
        }

        $rules = [
            'certification' => 'required|string|max:255',
            'specializations' => 'required|string|max:255',
            'portfolio_link' => 'nullable|url|max:255',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
        ];

        // If user is not authenticated, validate basic registration fields
        if (!Auth::check()) {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $userId = Auth::id();

            if (!$userId) {
                // Register new user first
                $user = new \App\Models\User();
                $user->name = trim($request->first_name . ' ' . $request->last_name);
                $user->email = $request->email;
                $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
                $user->save();

                // Assign default role
                $user->assignRole('user');

                // Create profile record
                \Illuminate\Support\Facades\DB::table('user_profiles')->insert([
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Automatically log in the user
                Auth::login($user);
                $userId = $user->id;
            }

            // Create/update partner request
            Partner::updateOrCreate(
                ['user_id' => $userId],
                [
                    'certification' => $request->certification,
                    'specializations' => array_map('trim', explode(',', $request->specializations)),
                    'portfolio_link' => $request->portfolio_link,
                    'bank_name' => $request->bank_name,
                    'bank_account_number' => $request->bank_account_number,
                    'bank_account_name' => $request->bank_account_name,
                    'is_verified' => false,
                ]
            );

            \Illuminate\Support\Facades\DB::commit();

            return redirect('/')->with('success', 'Pendaftaran berhasil dikirim! Silakan tunggu verifikasi dari tim kami. Anda akan dihubungi jika disetujui.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Mitra Registration Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()])->withInput();
        }
    }
}
