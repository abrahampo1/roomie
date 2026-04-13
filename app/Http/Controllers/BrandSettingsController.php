<?php

namespace App\Http\Controllers;

use App\Models\BrandSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandSettingsController extends Controller
{
    public function show(): View
    {
        $brand = auth()->user()->brandSetting ?? new BrandSetting;

        return view('settings.brand', compact('brand'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_name' => ['nullable', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'voice_description' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_website' => ['nullable', 'url:http,https', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        $user = auth()->user();
        $brand = $user->brandSetting ?? new BrandSetting(['user_id' => $user->id]);

        // Handle logo
        if ($request->boolean('remove_logo') && $brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
            $brand->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }
            $ext = $request->file('logo')->extension();
            $path = "brands/logos/{$user->id}/".Str::uuid().".{$ext}";
            $request->file('logo')->storeAs(dirname($path), basename($path), 'public');
            $brand->logo_path = $path;
        }

        $brand->brand_name = $validated['brand_name'] ?? null;
        $brand->primary_color = $validated['primary_color'] ?? null;
        $brand->secondary_color = $validated['secondary_color'] ?? null;
        $brand->voice_description = $validated['voice_description'] ?? null;
        $brand->contact_email = $validated['contact_email'] ?? null;
        $brand->contact_phone = $validated['contact_phone'] ?? null;
        $brand->contact_website = $validated['contact_website'] ?? null;
        $brand->social_links = array_filter($validated['social_links'] ?? []);
        $brand->save();

        return back()->with('message', 'Marca actualizada.');
    }
}
