<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Research;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ResearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Research::query()->with('author')->where('author_id', auth()->id());

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('ticker', 'like', '%' . $request->search . '%');
            });
        }

        $researches = $query->select([
            'id', 'title', 'ticker', 'sector', 'slug', 'subtitle', 'revenue', 
            'patmi', 'sales', 'tags', 'date', 'price', 'recommendation', 
            'target_price', 'upside', 'report_type', 'is_premium', 'pdf_path', 
            'image', 'author_id', 'created_at', 'updated_at'
        ])->latest()->paginate(10)->withQueryString();

        return Inertia::render('Mitra/Researches/Index', [
            'researches' => $researches,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Mitra/Researches/CreateEdit');
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

        return redirect()->route('mitra.researches.index')->with('success', 'Katalog Riset berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Research $research)
    {
        if ($research->author_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Mitra/Researches/CreateEdit', [
            'research' => $research
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Research $research)
    {
        if ($research->author_id !== auth()->id()) {
            abort(403);
        }

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
            if ($research->pdf_path) {
                $oldPath = str_replace('/storage/', '', $research->pdf_path);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $validated['pdf_path'] = '/storage/' . $request->file('pdf_path')->store('reports', 'public');
        } elseif ($request->has('pdf_path') && $request->pdf_path === null) {
            $validated['pdf_path'] = null; // Allow clearing
        } else {
            unset($validated['pdf_path']); // Keep existing if string
        }

        if ($request->hasFile('image')) {
            if ($research->image) {
                $oldPath = str_replace('/storage/', '', $research->image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $validated['image'] = '/storage/' . $request->file('image')->store('covers', 'public');
        } elseif ($request->has('image') && $request->image === null) {
            $validated['image'] = null; // Allow clearing
        } else {
            unset($validated['image']); // Keep existing if string
        }

        $validated['author_id'] = auth()->id();

        $research->update($validated);

        return redirect()->route('mitra.researches.index')->with('success', 'Katalog Riset berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Research $research)
    {
        if ($research->author_id !== auth()->id()) {
            abort(403);
        }

        if ($research->pdf_path) {
            $oldPath = str_replace('/storage/', '', $research->pdf_path);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        if ($research->image) {
            $oldPath = str_replace('/storage/', '', $research->image);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        $research->delete();
        
        return redirect()->route('mitra.researches.index')->with('success', 'Katalog Riset berhasil dihapus.');
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

        $researches = Research::whereIn('id', $request->ids)->where('author_id', auth()->id())->get();

        foreach ($researches as $research) {
            if ($research->pdf_path) {
                $oldPath = str_replace('/storage/', '', $research->pdf_path);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            if ($research->image) {
                $oldPath = str_replace('/storage/', '', $research->image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $research->delete();
        }

        return redirect()->back()->with('success', 'Katalog Riset terpilih berhasil dihapus.');
    }
}
