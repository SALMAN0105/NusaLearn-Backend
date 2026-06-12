<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Bahasa;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $languages = Bahasa::where('aktif', true)->get();
        $query = Materi::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('judul', 'LIKE', "%{$request->search}%");
        }

        if ($request->has('language_scope') && $request->language_scope != 'Semua Bahasa' && $request->language_scope != '') {
            $query->where('kode_bahasa', $request->language_scope);
        }

        $currentUser = auth()->user();
        if ($currentUser->peran === 'admin' || $currentUser->peran === 'guru') {
            $query->where('asal_sekolah', $currentUser->asal_sekolah);
        }

        $materials = $query->latest()->paginate(5);

        // In case the view expects Admin/materials or Administrator/materials
        // Route namespace determines this, but it's typically 'admin.materials' based on previous code.
        if ($currentUser->peran === 'administrator') {
            return view('administrator.materials', compact('materials', 'languages'));
        }
        return view('admin.materials', compact('materials', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|in:literasi,numerasi,budaya',
            'kelas' => 'required|integer|min:1|max:3',
            'tingkat_kesulitan' => 'required|integer|min:1|max:5',
            'kode_bahasa' => 'required',
            'json_file' => 'required|file|mimes:json,txt|max:2048', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $currentUser = auth()->user();

        $data = [
            'asal_sekolah' => ($currentUser->peran === 'admin' || $currentUser->peran === 'guru') ? $currentUser->asal_sekolah : null,
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'kelas' => $request->kelas,
            'tingkat_kesulitan' => $request->tingkat_kesulitan,
            'kode_bahasa' => $request->kode_bahasa,
        ];

        // Proses JSON
        if ($request->hasFile('json_file')) {
            $file = $request->file('json_file');
            $jsonContent = file_get_contents($file->getRealPath());
            $decoded = json_decode($jsonContent, true);
            
            if (!isset($decoded['metadata']) || !isset($decoded['knowledge_map'])) {
                return back()->withErrors(['json_file' => 'Struktur JSON tidak memenuhi standar Metadata-First (Missing knowledge_map).']);
            }

            $data['konten'] = $decoded;
        }

        // Upload Gambar
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('materials', 'public');
            $data['url_gambar'] = $path;
        }

        // Simpan ke database
        $material = Materi::create($data);

        // TRIGGER PYTHON SCRIPT OTOMATIS
        $this->processAI($material->id);

        return redirect()->back()
            ->with('success', 'Materi berhasil ditambahkan! AI sedang diproses di background.');
    }

    private function processAI($materialId)
    {
        try {
            $pythonScript = base_path('python/ai_processor.py');
            
            if (!file_exists($pythonScript)) {
                Log::error("Skrip tidak ditemukan di: " . $pythonScript);
                return;
            }

            $pythonBinary = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';

            dispatch(function() use ($pythonBinary, $pythonScript, $materialId) {
                $command = sprintf('"%s" "%s" %d', escapeshellarg($pythonBinary), escapeshellarg($pythonScript), $materialId);
                exec($command . ' 2>&1', $output, $returnVar);
                Log::info("AI Processor result for ID {$materialId}: \n" . implode("\n", $output));
            })->afterResponse();

            Log::info("AI processing dispatched for ID: {$materialId}");

        } catch (\Exception $e) {
            Log::error("Gagal trigger AI: " . $e->getMessage());
        }
    }

    public function regenerateAI($id)
    {
        $material = Materi::findOrFail($id);
        
        // Pencegahan IDOR
        $currentUser = auth()->user();
        if (($currentUser->peran === 'admin' || $currentUser->peran === 'guru') && $material->asal_sekolah !== $currentUser->asal_sekolah) {
            abort(403, 'Unauthorized Action. Anda tidak dapat memproses materi dari sekolah lain.');
        }

        $material->update(['status_ai' => 'pending', 'ai_embeddings' => null]);
        
        $this->processAI($material->id);
        
        return response()->json(['status' => 'success', 'message' => 'Regenerasi dimulai']);
    }

    public function update(Request $request, $id)
    {
        $material = Materi::findOrFail($id);

        // Pencegahan IDOR
        $currentUser = auth()->user();
        if (($currentUser->peran === 'admin' || $currentUser->peran === 'guru') && $material->asal_sekolah !== $currentUser->asal_sekolah) {
            abort(403, 'Unauthorized Action. Anda tidak dapat mengubah materi dari sekolah lain.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required',
            'kelas' => 'required|integer|min:1|max:3',
            'tingkat_kesulitan' => 'required|integer|min:1|max:5',
            'kode_bahasa' => 'required',
            'json_file' => 'nullable|file|mimes:json,txt|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $material->judul = $request->judul;
        $material->kategori = $request->kategori;
        $material->kelas = $request->kelas;
        $material->tingkat_kesulitan = $request->tingkat_kesulitan;
        $material->kode_bahasa = $request->kode_bahasa;

        // Cek Ganti JSON
        if ($request->hasFile('json_file')) {
            $file = $request->file('json_file');
            $jsonContent = file_get_contents($file->getRealPath());
            $decoded = json_decode($jsonContent, true);

            if ($decoded === null) {
                return back()->withErrors(['json_file' => 'File JSON baru tidak valid.']);
            }

            $material->konten = $decoded;
            $material->status_ai = 'pending';
        }

        // Cek Ganti Gambar
        if ($request->hasFile('image')) {
            if ($material->url_gambar && Storage::disk('public')->exists($material->url_gambar)) {
                Storage::disk('public')->delete($material->url_gambar);
            }
            
            $path = $request->file('image')->store('materials', 'public');
            $material->url_gambar = $path;
        }

        $material->save();

        if ($request->hasFile('json_file')) {
            $this->processAI($material->id);
        }

        return redirect()->back()->with('success', 'Data materi diperbarui!');
    }

    public function destroy($id)
    {
        $material = Materi::findOrFail($id);
        
        // Pencegahan IDOR
        $currentUser = auth()->user();
        if (($currentUser->peran === 'admin' || $currentUser->peran === 'guru') && $material->asal_sekolah !== $currentUser->asal_sekolah) {
            abort(403, 'Unauthorized Action. Anda tidak dapat menghapus materi dari sekolah lain.');
        }

        $material->delete();
        return redirect()->back()->with('success', 'Materi berhasil dihapus.');
    }
}
