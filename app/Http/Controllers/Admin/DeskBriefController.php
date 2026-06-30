<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeskBrief;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeskBriefController extends Controller
{
    public function index()
    {
        $deskBriefs = DeskBrief::with('analyst')->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        return Inertia::render('Admin/DeskBrief/Index', [
            'deskBriefs' => $deskBriefs
        ]);
    }

    public function edit($id)
    {
        $deskBrief = DeskBrief::findOrFail($id);
        
        return Inertia::render('Admin/DeskBrief/Edit', [
            'deskBrief' => $deskBrief
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'market_read' => 'nullable|string',
            'so_what' => 'nullable|string',
            'what_to_do' => 'nullable|string',
        ]);

        $deskBrief = DeskBrief::findOrFail($id);
        
        $deskBrief->update([
            'title' => $request->title,
            'market_read' => $request->market_read,
            'so_what' => $request->so_what,
            'what_to_do' => $request->what_to_do,
        ]);

        return redirect()->route('admin.desk-brief.index')->with('success', 'Desk Brief updated successfully.');
    }

    public function publish($id)
    {
        $deskBrief = DeskBrief::findOrFail($id);
        
        $deskBrief->update([
            'status' => 'published',
            'published_at' => now(),
            'analyst_id' => auth()->id()
        ]);

        return redirect()->route('admin.desk-brief.index')->with('success', 'Desk Brief published successfully.');
    }
}
