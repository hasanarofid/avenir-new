<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Research;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CommentController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'guest_name' => 'nullable|string|max:255',
        ]);

        $research = Research::findOrFail($id);
        
        $isLoggedIn = auth()->check();
        $ip = $request->ip();

        if (!$isLoggedIn) {
            // Check limit: 1 per day for guests
            $count = Comment::where('guest_ip', $ip)
                ->whereDate('created_at', Carbon::today())
                ->count();
                
            if ($count >= 1) {
                return back()->withErrors(['content' => 'Anda sudah memberikan komentar hari ini. Silakan daftar akun untuk komentar tanpa batas.']);
            }
        }

        Comment::create([
            'research_id' => $research->id,
            'user_id' => $isLoggedIn ? auth()->id() : null,
            'guest_name' => $isLoggedIn ? null : ($request->input('guest_name') ?: 'Guest'),
            'guest_ip' => $isLoggedIn ? null : $ip,
            'content' => $request->input('content'),
        ]);

        return back();
    }
}
