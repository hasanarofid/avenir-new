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

    public function verify(Request $request, $id)
    {
        $payment = \App\Models\PaymentSubmission::findOrFail($id);
        
        $payment->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id()
        ]);

        // Assign role 'subscriber' to user
        $user = \App\Models\User::find($payment->user_id);
        if ($user) {
            $user->assignRole('subscriber');
            
            // Juga update user_profiles if needed, tp role yg utama.
            \Illuminate\Support\Facades\DB::table('user_profiles')
                ->where('user_id', $user->id)
                ->update(['is_subscriber' => true]);
        }

        return redirect()->back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, $id)
    {
        $payment = \App\Models\PaymentSubmission::findOrFail($id);
        
        $payment->update([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Pembayaran ditolak.');
    }
}
