<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Research;

class InteractionController extends Controller
{
    private function getModel($type, $id)
    {
        if ($type === 'article') {
            return Article::findOrFail($id);
        } elseif ($type === 'research') {
            return Research::findOrFail($id);
        }
        abort(404);
    }

    public function toggleLike(Request $request, $type, $id)
    {
        $model = $this->getModel($type, $id);
        $userId = auth()->id();
        $ip = $request->ip();

        $like = $model->likes()->where(function($q) use ($userId, $ip) {
            if ($userId) {
                $q->where('user_id', $userId);
            } else {
                $q->where('guest_ip', $ip)->whereNull('user_id');
            }
        })->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            $model->likes()->create([
                'user_id' => $userId,
                'guest_ip' => $userId ? null : $ip,
            ]);
            $isLiked = true;
        }

        return response()->json([
            'is_liked' => $isLiked,
            'likes_count' => $model->likes()->count()
        ]);
    }

    public function addComment(Request $request, $type, $id)
    {
        $request->validate([
            'content' => 'required|string',
            'guest_name' => 'nullable|string|max:255'
        ]);

        $model = $this->getModel($type, $id);
        $userId = auth()->id();
        $ip = $request->ip();

        $data = [
            'user_id' => $userId,
            'guest_name' => $userId ? auth()->user()->name : ($request->guest_name ?: 'Guest'),
            'guest_ip' => $ip,
            'content' => $request->content,
        ];

        if ($type === 'research') {
            $model->polyComments()->create($data);
        } else {
            $model->comments()->create($data);
        }

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function incrementShare(Request $request, $type, $id)
    {
        $model = $this->getModel($type, $id);
        $userId = auth()->id();
        $ip = $request->ip();

        $model->shares()->create([
            'user_id' => $userId,
            'guest_ip' => $userId ? null : $ip,
        ]);

        return response()->json([
            'shares_count' => $model->shares()->count()
        ]);
    }
}
