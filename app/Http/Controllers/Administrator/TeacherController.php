<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeacherAccessCodeMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Pengguna::where('peran', 'admin')->latest()->paginate(5);
        $schools = \App\Models\Sekolah::orderBy('nama', 'asc')->get();
        return view('administrator.teachers', compact('teachers', 'schools'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:255|unique:pengguna',
            'email' => 'required|string|email|max:255|unique:pengguna',
            'asal_sekolah' => 'required|string|max:255',
            'kata_sandi' => 'required|string|min:8|confirmed',
        ]);

        $accessCode = strtoupper(Str::random(10));
        
        // Tentukan kode_bahasa dari sekolah
        $languageCode = 'id-1'; // Guru menggunakan Bahasa Indonesia

        $teacher = Pengguna::create([
            'nama' => $request->nama,
            'nama_pengguna' => $request->nama_pengguna,
            'email' => $request->email,
            'asal_sekolah' => $request->asal_sekolah,
            'peran' => 'admin', // admin is guru
            'kata_sandi' => Hash::make($request->kata_sandi),
            'kode_akses' => $accessCode,
            'kode_bahasa' => $languageCode,
            'aktif' => true,
        ]);

        try {
            Mail::to($teacher->email)->send(new TeacherAccessCodeMail($teacher, $accessCode));
            return redirect()->route('administrator.guru.index')->with('success', 'Guru berhasil ditambahkan dan Kode Akses otomatis dikirim ke email.');
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email: ' . $e->getMessage());
            return redirect()->route('administrator.guru.index')->with('success', 'Guru berhasil ditambahkan, namun gagal mengirim email kode akses. Pastikan konfigurasi email (SMTP) sudah benar.');
        }
    }

    public function update(Request $request, Pengguna $guru)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:255|unique:pengguna,nama_pengguna,' . $guru->id,
            'email' => 'required|string|email|max:255|unique:pengguna,email,' . $guru->id,
            'asal_sekolah' => 'required|string|max:255',
        ]);

        $data = $request->only(['nama', 'nama_pengguna', 'email', 'asal_sekolah']);
        
        if ($request->filled('password')) {
            $request->validate(['kata_sandi' => 'string|min:8|confirmed']);
            $data['kata_sandi'] = Hash::make($request->kata_sandi);
        }

        $guru->update($data);
        return redirect()->route('administrator.guru.index')->with('success', 'Data Guru berhasil diperbarui.');
    }

    public function destroy(Pengguna $guru)
    {
        $guru->delete();
        return redirect()->route('administrator.guru.index')->with('success', 'Data Guru berhasil dihapus.');
    }

    public function generateAccessCode(Request $request, Pengguna $guru)
    {
        $accessCode = strtoupper(Str::random(10));
        
        $guru->update([
            'kode_akses' => $accessCode,
        ]);

        try {
            Mail::to($guru->email)->send(new TeacherAccessCodeMail($guru, $accessCode));
            return back()->with('success', 'Kode Akses untuk guru ' . $guru->nama . ' berhasil diperbarui dan dikirim ke email.');
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email: ' . $e->getMessage());
            return back()->with('success', 'Kode Akses berhasil diperbarui, namun gagal mengirim email. Pastikan konfigurasi email (SMTP) sudah benar.');
        }
    }
}
