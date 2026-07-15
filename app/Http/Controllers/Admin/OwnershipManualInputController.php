<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OwnershipManualInput;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class OwnershipManualInputController extends Controller
{
    public function index()
    {
        $inputs = OwnershipManualInput::orderBy('updated_at', 'desc')->get();
        return Inertia::render('Admin/Ownership/ManualInput', [
            'inputs' => $inputs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticker' => 'required|string|max:10',
            'ubo_image' => 'nullable|image|max:10240', // max 10MB
            'shareholder_image' => 'nullable|image|max:10240',
        ]);

        $ticker = strtoupper($request->ticker);
        
        $input = OwnershipManualInput::firstOrNew(['ticker' => $ticker]);

        if ($request->hasFile('ubo_image')) {
            if ($input->ubo_image_path) {
                Storage::disk('public')->delete($input->ubo_image_path);
            }
            $input->ubo_image_path = $request->file('ubo_image')->store('ownership_manual_inputs', 'public');
        }

        if ($request->hasFile('shareholder_image')) {
            if ($input->shareholder_image_path) {
                Storage::disk('public')->delete($input->shareholder_image_path);
            }
            $input->shareholder_image_path = $request->file('shareholder_image')->store('ownership_manual_inputs', 'public');
        }

        $input->save();

        return redirect()->back()->with('success', 'Manual input saved for ' . $ticker);
    }

    public function destroy(OwnershipManualInput $input)
    {
        if ($input->ubo_image_path) {
            Storage::disk('public')->delete($input->ubo_image_path);
        }
        if ($input->shareholder_image_path) {
            Storage::disk('public')->delete($input->shareholder_image_path);
        }
        
        $input->delete();
        
        return redirect()->back()->with('success', 'Manual input deleted');
    }
}
