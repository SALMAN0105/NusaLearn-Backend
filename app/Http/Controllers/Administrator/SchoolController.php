<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = Sekolah::latest()->paginate(5);
        return view('administrator.schools', compact('schools'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'kode_pos' => 'nullable|string|max:20',
        ]);

        Sekolah::create($request->all());
        return redirect()->route('administrator.sekolah.index')->with('success', 'Sekolah berhasil ditambahkan.');
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'kode_pos' => 'nullable|string|max:20',
        ]);

        $sekolah->update($request->all());
        return redirect()->route('administrator.sekolah.index')->with('success', 'Sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete();
        return redirect()->route('administrator.sekolah.index')->with('success', 'Sekolah berhasil dihapus.');
    }
}
