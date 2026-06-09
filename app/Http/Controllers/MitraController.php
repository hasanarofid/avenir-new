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
        $user = Auth::user()->load('partner');
        $researches = Research::where('author_id', $user->id)->orderBy('created_at', 'desc')->get();
        $articles = Article::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Mitra/Dashboard', [
            'researches' => $researches,
            'articles' => $articles,
        ]);
    }

    public function researches()
    {
        $researches = Research::where('author_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return Inertia::render('Mitra/Researches', [
            'researches' => $researches,
        ]);
    }

    public function profile()
    {
        $user = Auth::user()->load('partner');
        return Inertia::render('Mitra/Profile', [
            'user' => $user,
            'partner' => $user->partner,
        ]);
    }

    public function create()
    {
        return Inertia::render('Mitra/Register');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certification' => 'required|string|max:255',
            'specializations' => 'required|string|max:255',
            'portfolio_link' => 'nullable|url|max:255',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $partner = Partner::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'certification' => $request->certification,
                'specializations' => explode(',', $request->specializations),
                'portfolio_link' => $request->portfolio_link,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
                'is_verified' => false,
            ]
        );

        return redirect()->route('mitra.dashboard')->with('success', 'Pendaftaran berhasil dikirim! Silakan tunggu verifikasi dari tim kami.');
    }
}
