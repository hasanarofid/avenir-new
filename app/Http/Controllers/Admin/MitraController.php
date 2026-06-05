<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MitraController extends Controller
{
    public function index()
    {
        $mitra = DB::table('partners')
            ->leftJoin('users', 'partners.user_id', '=', 'users.id')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select(
                'partners.*',
                'users.name',
                'users.email',
                'user_profiles.phone_number',
                'user_profiles.avatar_url'
            )
            ->orderBy('partners.created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Mitra/Index', [
            'mitra' => $mitra
        ]);
    }
}
