<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsUpdateRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $this->assertAdmin();

        $settings = [
            'site_name' => Setting::get('site_name', config('app.name', 'HK Checklist')),
            'theme_color' => Setting::get('theme_color', '#842eb8'),
            'application_logo_path' => Setting::get('application_logo_path'),
            'favicon_path' => Setting::get('favicon_path'),
            'button_primary_color' => Setting::get('button_primary_color', '#842eb8'),
            'button_success_color' => Setting::get('button_success_color', '#10b981'),
            'button_danger_color' => Setting::get('button_danger_color', '#ef4444'),
            'button_warning_color' => Setting::get('button_warning_color', '#f59e0b'),
            'button_info_color' => Setting::get('button_info_color', '#06b6d4'),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update the settings.
     */
    public function update(SettingsUpdateRequest $request)
    {
        $this->assertAdmin();

        // Handle logo upload
        if ($request->hasFile('application_logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::get('application_logo_path');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $logoPath = $request->file('application_logo')->store('logos', 'public');
            Setting::set('application_logo_path', $logoPath);
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            $oldFavicon = Setting::get('favicon_path');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }

            // Store new favicon
            $faviconPath = $request->file('favicon')->store('favicons', 'public');
            Setting::set('favicon_path', $faviconPath);
        }

        // Update site name
        Setting::set('site_name', $request->site_name);

        // Update theme color
        Setting::set('theme_color', $request->theme_color);

        // Update button variant colors
        if ($request->filled('button_primary_color')) {
            Setting::set('button_primary_color', $request->button_primary_color);
        }
        if ($request->filled('button_success_color')) {
            Setting::set('button_success_color', $request->button_success_color);
        }
        if ($request->filled('button_danger_color')) {
            Setting::set('button_danger_color', $request->button_danger_color);
        }
        if ($request->filled('button_warning_color')) {
            Setting::set('button_warning_color', $request->button_warning_color);
        }
        if ($request->filled('button_info_color')) {
            Setting::set('button_info_color', $request->button_info_color);
        }

        // Clear cache
        Setting::clearCache();

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Assert that the current user is an admin.
     */
    private function assertAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Only administrators can access settings.');
        }
    }
}
