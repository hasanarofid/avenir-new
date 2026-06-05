<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('users')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->leftJoin('trial_email_history', 'users.email', '=', 'trial_email_history.email_lower')
            ->select(
                'users.id', 
                'users.name', 
                'users.email', 
                'users.created_at',
                'user_profiles.is_subscriber',
                'user_profiles.phone_number',
                'trial_email_history.created_at as trial_started_at'
            )
            ->orderBy('users.created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users
        ]);
    }
}
