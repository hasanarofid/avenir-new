<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\DeskBrief;

class DeskBriefController extends Controller
{
    public function index()
    {
        $latestBrief = DeskBrief::with(['marketStance', 'drivers', 'radarStocks'])
            ->where('status', 'published')
            ->orderBy('date', 'desc')
            ->first();

        return Inertia::render('DeskBrief/Index', [
            'deskBrief' => $latestBrief,
        ]);
    }
}
