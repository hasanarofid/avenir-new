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
        $deskBriefs = DeskBrief::with(['analyst', 'marketStance'])->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        return Inertia::render('Admin/DeskBrief/Index', [
            'deskBriefs' => $deskBriefs
        ]);
    }

    public function edit($id)
    {
        $deskBrief = DeskBrief::with('marketStance')->findOrFail($id);
        
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
            'momentum_score' => 'nullable|numeric|min:0|max:100',
            'breadth_score' => 'nullable|numeric|min:0|max:100',
            'foreign_score' => 'nullable|numeric|min:0|max:100',
            'sector_score' => 'nullable|numeric|min:0|max:100',
            'rupiah_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $deskBrief = DeskBrief::findOrFail($id);
        
        $deskBrief->update([
            'title' => $request->title,
            'market_read' => $request->market_read,
            'so_what' => $request->so_what,
            'what_to_do' => $request->what_to_do,
        ]);

        if ($deskBrief->market_stance_id && $request->has('momentum_score')) {
            $stance = \App\Models\MarketStanceDaily::find($deskBrief->market_stance_id);
            if ($stance) {
                $stance->momentum_score = $request->momentum_score;
                $stance->breadth_score = $request->breadth_score;
                $stance->foreign_score = $request->foreign_score;
                $stance->sector_score = $request->sector_score;
                $stance->rupiah_score = $request->rupiah_score;
                
                // Recalculate Total Score
                $totalScore = ($request->momentum_score * 0.3) +
                              ($request->breadth_score * 0.25) +
                              ($request->foreign_score * 0.2) +
                              ($request->sector_score * 0.15) +
                              ($request->rupiah_score * 0.1);
                              
                $stance->score = round($totalScore);
                $stance->save();
            }
        }

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
