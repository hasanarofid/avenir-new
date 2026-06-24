<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionPackageController extends Controller
{
    /**
     * Display a listing of the packages.
     */
    public function index()
    {
        $packages = SubscriptionPackage::all()->map(function ($pkg) {
            return [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'price' => $pkg->price,
                'active_price' => $pkg->active_price,
                'has_active_discount' => $pkg->has_active_discount,
                'discount_percent' => $pkg->discount_percent,
                'discount_end_at' => $pkg->discount_end_at ? $pkg->discount_end_at->format('Y-m-d\TH:i') : null,
                'duration_days' => $pkg->duration_days,
                'is_active' => $pkg->is_active,
                'badge' => $pkg->badge,
                'save_text' => $pkg->save_text,
            ];
        });

        return Inertia::render('Admin/Packages/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Update the specified package in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
            'discount_end_at' => 'nullable|date',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $package = SubscriptionPackage::findOrFail($id);

        $package->update([
            'name' => $request->name,
            'price' => $request->price,
            'discount_percent' => $request->discount_percent,
            'discount_end_at' => $request->discount_end_at,
            'duration_days' => $request->duration_days,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Paket langganan berhasil diperbarui.');
    }
}
