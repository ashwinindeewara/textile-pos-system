<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAllSettings();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_address' => 'required|string|max:1000',
            'phone_number' => 'required|string|max:100',
            'currency_symbol' => 'required|string|max:10',
            'receipt_footer' => 'required|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        Setting::set('shop_name', $validated['shop_name']);
        Setting::set('shop_address', $validated['shop_address']);
        Setting::set('phone_number', $validated['phone_number']);
        Setting::set('currency_symbol', $validated['currency_symbol']);
        Setting::set('receipt_footer', $validated['receipt_footer']);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            Setting::set('logo_path', 'storage/' . $path);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Shop settings updated successfully!');
    }
}
