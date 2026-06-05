<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = DB::table('payment_submissions')
            ->leftJoin('users', 'payment_submissions.user_id', '=', 'users.id')
            ->select(
                'payment_submissions.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('payment_submissions.created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments
        ]);
    }
}
