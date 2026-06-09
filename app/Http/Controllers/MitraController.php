<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Research;
use App\Models\Article;
use App\Models\PaymentSubmission;
use Illuminate\Support\Facades\Auth;

class MitraController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
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
}
