<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        // Pluck key-value settings to make it easier to work with on the frontend
        $settings = Setting::all()->pluck('value', 'key');
        
        // We'll also append the full URL of the site logo if it exists
        if (!empty($settings['site_logo'])) {
            $settings['site_logo_url'] = Storage::url($settings['site_logo']);
        } else {
            $settings['site_logo_url'] = null;
        }

        return Inertia::render('Admin/Settings', [
            'settings' => $settings
        ]);
    }

    /**
     * Update the global settings.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'site_name'           => 'required|string|max:100',
            'site_description'    => 'nullable|string|max:500',
            'whatsapp_number'     => 'nullable|string|max:20',
            'playstore_link'      => 'nullable|url|max:255',
            'bank_account_info'   => 'nullable|string|max:500',
            'site_logo'           => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'trial_artikel_limit' => 'nullable|integer|min:0|max:100',
            'trial_riset_limit'   => 'nullable|integer|min:0|max:100',
        ]);

        // Process site logo file upload
        if ($request->hasFile('site_logo')) {
            $oldLogoSetting = Setting::where('key', 'site_logo')->first();
            $oldLogoPath = $oldLogoSetting ? $oldLogoSetting->value : null;
            
            // Delete old file from storage if it exists
            if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                Storage::disk('public')->delete($oldLogoPath);
            }

            // Save new logo
            $path = $request->file('site_logo')->store('settings', 'public');
            Setting::setValue('site_logo', $path, 'image');
        }

        // Process standard settings
        Setting::setValue('site_name', $validatedData['site_name'], 'text');
        Setting::setValue('site_description', $validatedData['site_description'] ?? '', 'textarea');
        Setting::setValue('whatsapp_number', $validatedData['whatsapp_number'] ?? '', 'text');
        Setting::setValue('playstore_link', $validatedData['playstore_link'] ?? '', 'url');
        Setting::setValue('bank_account_info', $validatedData['bank_account_info'] ?? '', 'textarea');

        // Trial access limits
        Setting::setValue('trial_artikel_limit', (string) ($validatedData['trial_artikel_limit'] ?? 3), 'number');
        Setting::setValue('trial_riset_limit',   (string) ($validatedData['trial_riset_limit']   ?? 3), 'number');

        return redirect()->back()->with('success', 'Konfigurasi website berhasil diperbarui.');
    }
}
