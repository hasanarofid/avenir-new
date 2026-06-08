<?php

namespace App\Http\Controllers;

use App\Models\KIBrief;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KIBriefController extends Controller
{
    public function index()
    {
        $kiBriefs = KIBrief::with(['disclosure.ticker'])
            ->latest()
            ->paginate(15);

        return Inertia::render('KIBrief/Index', [
            'kiBriefs' => $kiBriefs
        ]);
    }
}
