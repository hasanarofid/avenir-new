<?php

namespace App\Http\Controllers;

use App\Models\Disclosure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisclosureController extends Controller
{
    public function index()
    {
        $disclosures = Disclosure::with(['ticker', 'kiBrief'])
            ->latest('date')
            ->paginate(20);

        return Inertia::render('Disclosure/Index', [
            'disclosures' => $disclosures
        ]);
    }
}
