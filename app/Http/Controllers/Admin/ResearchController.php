<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Research;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ResearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Research::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('ticker', 'like', '%' . $request->search . '%');
        }

        $researches = $query->select([
            'id', 'title', 'ticker', 'sector', 'slug', 'subtitle', 'revenue', 
            'patmi', 'sales', 'tags', 'date', 'price', 'recommendation', 
            'target_price', 'upside', 'report_type', 'is_premium', 'pdf_path', 
            'image', 'author_id', 'created_at', 'updated_at'
        ])->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Research/Index', [
            'researches' => $researches,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Research/CreateEdit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'ticker' => 'nullable|string|max:50',
            'sector' => 'nullable|string|max:255',
            'recommendation' => 'nullable|string|max:50',
            'target_price' => 'nullable|string|max:255',
            'upside' => 'nullable|string|max:50',
            'report_type' => 'nullable|string|max:255',
            'is_premium' => 'boolean',
            'pdf_path' => 'nullable',
            'image' => 'nullable',
            'content' => 'nullable|string',
            'subtitle' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        $validated['author_id'] = auth()->id();

        if ($request->hasFile('pdf_path')) {
            $validated['pdf_path'] = '/storage/' . $request->file('pdf_path')->store('reports', 'public');
        }

        if ($request->hasFile('image')) {
            $validated['image'] = '/storage/' . $request->file('image')->store('covers', 'public');
        }

        Research::create($validated);

        return redirect()->route('admin.katalog-riset.index')->with('success', 'Katalog Riset berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Research $katalog_riset)
    {
        return Inertia::render('Admin/Research/CreateEdit', [
            'research' => $katalog_riset
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Research $katalog_riset)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'ticker' => 'nullable|string|max:50',
            'sector' => 'nullable|string|max:255',
            'recommendation' => 'nullable|string|max:50',
            'target_price' => 'nullable|string|max:255',
            'upside' => 'nullable|string|max:50',
            'report_type' => 'nullable|string|max:255',
            'is_premium' => 'boolean',
            'pdf_path' => 'nullable',
            'image' => 'nullable',
            'content' => 'nullable|string',
            'subtitle' => 'nullable|string',
        ]);

        if ($request->hasFile('pdf_path')) {
            $validated['pdf_path'] = '/storage/' . $request->file('pdf_path')->store('reports', 'public');
        } elseif ($request->has('pdf_path') && $request->pdf_path === null) {
            $validated['pdf_path'] = null; // Allow clearing
        } else {
            unset($validated['pdf_path']); // Keep existing if string
        }

        if ($request->hasFile('image')) {
            $validated['image'] = '/storage/' . $request->file('image')->store('covers', 'public');
        } elseif ($request->has('image') && $request->image === null) {
            $validated['image'] = null; // Allow clearing
        } else {
            unset($validated['image']); // Keep existing if string
        }

        $katalog_riset->update($validated);

        return redirect()->route('admin.katalog-riset.index')->with('success', 'Katalog Riset berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Research $katalog_riset)
    {
        $katalog_riset->delete();
        return redirect()->route('admin.katalog-riset.index')->with('success', 'Katalog Riset berhasil dihapus.');
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:research,id',
        ]);

        Research::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' Katalog Riset berhasil dihapus.');
    }
}
