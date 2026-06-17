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
            'market_top_tickers'       => 'nullable|string|max:500',
            'market_watchlist_tickers' => 'nullable|string|max:500',
            'market_trending_tickers'  => 'nullable|string|max:500',
            'openrouter_api_key'        => 'nullable|string|max:255',
            'openrouter_default_model'  => 'nullable|string|max:100',
            'openrouter_fallback_model' => 'nullable|string|max:100',
            'maint_home'          => 'nullable|boolean',
            'maint_katalog'       => 'nullable|boolean',
            'maint_artikel'       => 'nullable|boolean',
            'maint_news'          => 'nullable|boolean',
            'maint_emiten'        => 'nullable|boolean',
            'maint_ki_brief'      => 'nullable|boolean',
            'maint_disclosure'    => 'nullable|boolean',
            'maint_tentang'       => 'nullable|boolean',
            'maint_mitra'         => 'nullable|boolean',
            'maint_langganan'     => 'nullable|boolean',
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

        // AI Configuration
        Setting::setValue('openrouter_api_key', $validatedData['openrouter_api_key'] ?? '', 'text');
        Setting::setValue('openrouter_default_model', $validatedData['openrouter_default_model'] ?? 'anthropic/claude-3.5-sonnet', 'text');
        Setting::setValue('openrouter_fallback_model', $validatedData['openrouter_fallback_model'] ?? 'openai/gpt-4o', 'text');

        // Market Config
        Setting::setValue('market_top_tickers', $validatedData['market_top_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, AMMN.JK, MDKA.JK', 'text');
        Setting::setValue('market_watchlist_tickers', $validatedData['market_watchlist_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, MDKA.JK', 'text');
        Setting::setValue('market_trending_tickers', $validatedData['market_trending_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, MDKA.JK, AMMN.JK, GOTO.JK', 'text');

        // Granular Maintenance Mode
        $maintKeys = ['maint_home', 'maint_katalog', 'maint_artikel', 'maint_news', 'maint_emiten', 'maint_ki_brief', 'maint_disclosure', 'maint_tentang', 'maint_mitra', 'maint_langganan'];
        foreach ($maintKeys as $mKey) {
            Setting::setValue($mKey, (bool) ($validatedData[$mKey] ?? false) ? '1' : '0', 'boolean');
        }

        return redirect()->back()->with('success', 'Konfigurasi website berhasil diperbarui.');
    }
}
