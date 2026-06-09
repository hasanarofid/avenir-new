<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Partner;
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

    public function approve($id)
    {
        $partner = Partner::findOrFail($id);
        $partner->update(['is_verified' => true]);
        
        // Assign role 'mitra' ke user
        $partner->user->assignRole('mitra');

        return redirect()->back()->with('success', 'Mitra berhasil diverifikasi!');
    }

    public function update(Request $request, $id)
    {
        $partner = Partner::findOrFail($id);
        
        $validated = $request->validate([
            'certification' => 'required|string|max:255',
            'specializations' => 'required|string|max:255',
            'portfolio_link' => 'nullable|url|max:255',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
        ]);

        $partner->update([
            'certification' => $validated['certification'],
            'specializations' => explode(',', $validated['specializations']),
            'portfolio_link' => $validated['portfolio_link'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_name' => $validated['bank_account_name'],
        ]);

        return redirect()->back()->with('success', 'Data mitra berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();

        return redirect()->back()->with('success', 'Mitra berhasil dihapus!');
    }
}
