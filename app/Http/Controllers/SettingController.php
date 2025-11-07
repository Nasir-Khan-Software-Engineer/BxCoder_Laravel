<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        try {
            $setting = Setting::first(); // assume single row
            return view('settings.index', compact('setting'));
        } catch (\Throwable $e) {
            Log::error('Settings Index Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to load settings.');
        }
    }

    /**
     * Update settings via AJAX or form submission
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'site_name' => 'required|string|max:255',
                'site_email' => 'nullable|email|max:255',
                'site_phone' => 'nullable|string|max:50',
                'meta_title' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'copyright' => 'nullable|string',
                'facebook' => 'nullable|url',
                'twitter' => 'nullable|url',
                'instagram' => 'nullable|url',
                'linkedin' => 'nullable|url',
                'youtube' => 'nullable|url'
            ]);

            $setting = Setting::first();

            if (!$setting) {
                return response()->json(['status' => 'error', 'message' => 'Settings not found'], 404);
            }

            $setting->update(array_merge(
                $request->only([
                    'site_name',
                    'site_email',
                    'site_phone',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'copyright',
                    'facebook',
                    'twitter',
                    'instagram',
                    'linkedin',
                    'youtube'
                ]),
                ['updated_by' => Auth::id()]
            ));

            return response()->json(['status' => 'success', 'message' => 'Settings updated successfully', 'setting' => $setting]);

        } catch (\Throwable $e) {
            Log::error('Settings Update Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to update settings'], 500);
        }
    }
}
