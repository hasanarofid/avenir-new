<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AILogController extends Controller
{
    public function index()
    {
        $logs = \App\Models\AILog::latest()->paginate(15);
        return \Inertia\Inertia::render('Admin/AILogs/Index', [
            'logs' => $logs
        ]);
    }
}
