<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Language;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::latest()->paginate(10);
        $languages = Language::where('is_active', true)->get(); // Untuk dropdown modal
        
        return view('admin.regions', compact('regions', 'languages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'postal_code' => 'required|string|unique:regions,postal_code|max:10',
            'district_name' => 'required|string|max:100',
            'language_code' => 'required|exists:languages,code',
        ]);

        Region::create($validated);

        return back()->with('success', 'Wilayah baru berhasil dipetakan.');
    }

    public function destroy(Region $region)
    {
        $region->delete();
        return back()->with('success', 'Data wilayah dihapus.');
    }
}