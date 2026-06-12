<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use App\Models\Bahasa;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Wilayah::with('sekolah')->latest()->paginate(5);
        $languages = Bahasa::where('aktif', true)->get(); 
        
        $registeredPostalCodes = Wilayah::pluck('kode_pos')->toArray();
        
        $schoolPostalCodes = \App\Models\Sekolah::whereNotNull('kode_pos')
                                ->whereNotIn('kode_pos', $registeredPostalCodes)
                                ->orderBy('nama')
                                ->get();
                                
        $allSchoolPostalCodes = \App\Models\Sekolah::whereNotNull('kode_pos')
                                ->orderBy('nama')
                                ->get();
        
        return view('administrator.regions', compact('regions', 'languages', 'schoolPostalCodes', 'allSchoolPostalCodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_pos' => 'required|string|max:10',
            'nama_kecamatan' => 'required|string|max:100',
            'kode_bahasa' => 'required|exists:bahasa,kode',
        ]);

        $existing = Wilayah::where('kode_pos', $request->kode_pos)->first();
        if ($existing) {
            if ($existing->kode_bahasa !== $request->kode_bahasa) {
                return back()->withErrors(['kode_pos' => 'Kode pos ini sudah terdaftar dengan bahasa ' . strtoupper($existing->kode_bahasa) . '. Seluruh sekolah dalam kode pos yang sama WAJIB menggunakan bahasa daerah yang sama.']);
            }
            return back()->with('success', 'Sekolah ini sudah otomatis tergabung dalam wilayah pemetaan ' . $existing->nama_kecamatan . '. Tidak perlu dipetakan ulang!');
        }

        Wilayah::create([
            'kode_pos'   => $request->kode_pos,
            'nama_kecamatan' => $request->nama_kecamatan,
            'kode_bahasa' => $request->kode_bahasa,
        ]);

        return back()->with('success', 'Wilayah baru berhasil dipetakan secara manual.');
    }

    public function update(Request $request, Wilayah $wilayah)
    {
        $validated = $request->validate([
            'kode_pos' => 'required|string|max:10|unique:wilayah,kode_pos,' . $wilayah->id,
            'nama_kecamatan' => 'required|string|max:100',
            'kode_bahasa' => 'required|exists:bahasa,kode',
        ]);
 
        $wilayah->update($validated);
 
        return back()->with('success', 'Data wilayah berhasil diperbarui.');
    }

    public function destroy(Wilayah $wilayah)
    {
        $wilayah->delete();
        return back()->with('success', 'Data wilayah dihapus.');
    }
}
