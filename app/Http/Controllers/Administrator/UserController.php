<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengguna;
use App\Models\Sekolah;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengguna::query();

        // Fitur Pencarian
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nama_pengguna', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Fitur Filter Role
        if ($request->filled('role')) {
            $query->where('peran', $request->input('role'));
        }

        $users = $query->latest()->paginate(5)->withQueryString();
        $schools = Sekolah::orderBy('nama', 'asc')->get();
        $regions = Wilayah::orderBy('kode_pos', 'asc')->get();

        return view('administrator.users', compact('users', 'schools', 'regions'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:255|unique:pengguna',
            'email' => 'nullable|string|email|max:255|unique:pengguna',
            'peran' => 'required|in:administrator,admin,siswa',
            'kata_sandi' => 'required|string|min:8|confirmed',
        ];

        // Validasi Kondisional berdasarkan Role
        if ($request->peran === 'admin') {
            $rules['asal_sekolah'] = 'required|string|max:255';
        } elseif ($request->peran === 'siswa') {
            $rules['asal_sekolah'] = 'required|string|max:255';
            $rules['kode_pos'] = 'required|string|max:10';
        }

        $request->validate($rules);

        // Tentukan kode_bahasa berdasarkan Role
        $defaultBahasa = \App\Models\Bahasa::first();
        $languageCode = $defaultBahasa ? $defaultBahasa->kode : 'tk-1';

        if ($request->peran === 'siswa') {
            $region = Wilayah::where('kode_pos', $request->kode_pos)->first();
            if (!$region) {
                return back()->withErrors(['kode_pos' => 'Kode pos tidak terdaftar di sistem.'])->withInput();
            }
            $languageCode = $region->kode_bahasa;
        } elseif ($request->peran === 'admin') {
            $languageCode = 'id-1'; // Guru pakai Bahasa Indonesia
        } elseif ($request->peran === 'administrator') {
            $languageCode = 'id-1'; // Admin pakai Bahasa Indonesia
        }

        // Tentukan access_code jika role admin (guru)
        $accessCode = null;
        if ($request->peran === 'admin') {
            $accessCode = strtoupper(Str::random(10));
        }

        Pengguna::create([
            'nama' => $request->nama,
            'nama_pengguna' => $request->nama_pengguna,
            'email' => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'peran' => $request->peran,
            'asal_sekolah' => in_array($request->peran, ['admin', 'siswa']) ? $request->asal_sekolah : null,
            'kode_pos' => $request->peran === 'siswa' ? $request->kode_pos : null,
            'kode_bahasa' => $languageCode,
            'kode_akses' => $accessCode,
            'aktif' => true,
        ]);

        return redirect()->route('administrator.pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, Pengguna $pengguna)
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'nama_pengguna' => 'required|string|max:255|unique:pengguna,nama_pengguna,' . $pengguna->id,
            'email' => 'nullable|string|email|max:255|unique:pengguna,email,' . $pengguna->id,
            'peran' => 'required|in:administrator,admin,siswa',
        ];

        // Validasi Kondisional berdasarkan Role
        if ($request->peran === 'admin') {
            $rules['asal_sekolah'] = 'required|string|max:255';
        } elseif ($request->peran === 'siswa') {
            $rules['asal_sekolah'] = 'required|string|max:255';
            $rules['kode_pos'] = 'required|string|max:10';
        }

        if ($request->filled('password')) {
            $rules['kata_sandi'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        // Cari Region jika role siswa
        $languageCode = $pengguna->kode_bahasa;
        if ($request->peran === 'siswa') {
            if ($request->kode_pos !== $pengguna->kode_pos) {
                $region = Wilayah::where('kode_pos', $request->kode_pos)->first();
                if (!$region) {
                    return back()->withErrors(['kode_pos' => 'Kode pos tidak terdaftar di sistem.'])->withInput();
                }
                $languageCode = $region->kode_bahasa;
            }
        } elseif ($request->peran === 'admin') {
            $languageCode = 'id-1'; // Guru pakai Bahasa Indonesia
        } elseif ($request->peran === 'administrator') {
            $languageCode = 'id-1'; // Admin pakai Bahasa Indonesia
        }

        $data = [
            'nama' => $request->nama,
            'nama_pengguna' => $request->nama_pengguna,
            'email' => $request->email,
            'peran' => $request->peran,
            'asal_sekolah' => in_array($request->peran, ['admin', 'siswa']) ? $request->asal_sekolah : null,
            'kode_pos' => $request->peran === 'siswa' ? $request->kode_pos : null,
            'kode_bahasa' => $languageCode,
        ];

        // Jika password diisi
        if ($request->filled('password')) {
            $data['kata_sandi'] = Hash::make($request->kata_sandi);
        }

        // Jika role berubah jadi admin dan tidak punya access_code sebelumnya
        if ($request->peran === 'admin' && empty($pengguna->kode_akses)) {
            $data['kode_akses'] = strtoupper(Str::random(10));
        } elseif ($request->peran !== 'admin') {
            $data['kode_akses'] = null;
        }

        $pengguna->update($data);

        return redirect()->route('administrator.pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Pengguna $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $pengguna->delete();
        return redirect()->route('administrator.pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function toggleActive(Pengguna $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $pengguna->update([
            'aktif' => !$pengguna->aktif
        ]);

        $status = $pengguna->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun pengguna {$pengguna->nama} berhasil {$status}.");
    }
}
