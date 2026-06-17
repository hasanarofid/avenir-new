<?php

namespace App\Http\Controllers;

use App\Models\Research;
use App\Models\ResearchBookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Toggle bookmark status untuk research yang diberikan.
     * Hanya bisa diakses oleh user yang sudah login.
     */
    public function toggle(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $research = Research::findOrFail($id);
        $userId = auth()->id();

        $existing = ResearchBookmark::where('research_id', $research->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isBookmarked = false;
        } else {
            ResearchBookmark::create([
                'research_id' => $research->id,
                'user_id'     => $userId,
            ]);
            $isBookmarked = true;
        }

        $bookmarkCount = ResearchBookmark::where('research_id', $research->id)->count();

        return response()->json([
            'is_bookmarked'  => $isBookmarked,
            'bookmark_count' => $bookmarkCount,
        ]);
    }
}
