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
        $currentMonth = now()->month;
        $currentYear = now()->year;

        /** @var \App\Models\User $user */
        $user = Auth::user()->load('partner');
        
        try {
            $researches = Research::where('author_id', $user->id)
                ->withCount(['likes', 'comments', 'shares'])
                ->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $researches = [];
        }
        
        try {
            $articles = Article::where('user_id', $user->id)
                ->withCount(['likes', 'comments', 'shares'])
                ->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $articles = [];
        }
        
        $incomeService = new \App\Services\MitraIncomeService();
        $monthlyIncome = $incomeService->calculateMonthlyIncome($user->id, $currentMonth, $currentYear);
        $cumulativeIncome = $incomeService->calculateCumulativeIncome($user->id);

        return Inertia::render('Mitra/Dashboard', [
            'researches' => $researches,
            'articles' => $articles,
            'monthlyIncome' => $monthlyIncome,
            'cumulativeIncome' => $cumulativeIncome,
            'currentPeriod' => now()->format('F Y')
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->partner) {
            if ($user->partner->is_verified) {
                return redirect()->route('mitra.dashboard');
            }
            return redirect('/profile')->with('info', 'Anda sudah melakukan pengajuan pendaftaran mitra.');
        }
        return Inertia::render('Mitra/Register');
    }

    public function store(Request $request)
    {
        if (Auth::user()->partner) {
            return redirect('/profile')->with('info', 'Anda sudah melakukan pengajuan pendaftaran mitra.');
        }

        $rules = [
            'certification' => 'required|string|max:255',
            'specializations' => 'required|string|max:255',
            'portfolio_link' => 'required_without:portfolio_pdf|nullable|url|max:255',
            'portfolio_pdf' => 'required_without:portfolio_link|nullable|file|mimes:pdf|max:10240',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $userId = Auth::id();
            
            $portfolioPdfPath = null;
            if ($request->hasFile('portfolio_pdf')) {
                $portfolioPdfPath = $request->file('portfolio_pdf')->store('portfolios', 'public');
            }

            // Create/update partner request
            Partner::updateOrCreate(
                ['user_id' => $userId],
                [
                    'certification' => $request->certification,
                    'specializations' => array_map('trim', explode(',', $request->specializations)),
                    'portfolio_link' => $request->portfolio_link,
                    'portfolio_pdf' => $portfolioPdfPath,
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
