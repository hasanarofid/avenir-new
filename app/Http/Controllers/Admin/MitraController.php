<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('partners')
            ->leftJoin('users', 'partners.user_id', '=', 'users.id')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select(
                'partners.*',
                'users.name',
                'users.email',
                'user_profiles.phone_number',
                'user_profiles.avatar_url'
            )
            ->orderBy('partners.created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', '%' . $search . '%')
                  ->orWhere('users.email', 'like', '%' . $search . '%')
                  ->orWhere('user_profiles.phone_number', 'like', '%' . $search . '%');
            });
        }

        $mitra = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Mitra/Index', [
            'mitra' => $mitra,
            'filters' => $request->only('search')
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
            'phone_number' => 'nullable|string|max:20',
        ]);

        $partner->update([
            'certification' => $validated['certification'],
            'specializations' => array_map('trim', explode(',', $validated['specializations'])),
            'portfolio_link' => $validated['portfolio_link'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_name' => $validated['bank_account_name'],
        ]);

        if (array_key_exists('phone_number', $validated)) {
            DB::table('user_profiles')->updateOrInsert(
                ['user_id' => $partner->user_id],
                ['phone_number' => $validated['phone_number'], 'updated_at' => now()]
            );
        }

        return redirect()->back()->with('success', 'Data mitra berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();

        return redirect()->back()->with('success', 'Mitra berhasil dihapus!');
    }
}
