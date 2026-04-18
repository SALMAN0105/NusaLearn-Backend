<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $languages = Language::where('is_active', true)->get();
        $query = Material::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('title_indo', 'LIKE', "%{$request->search}%");
        }

        if ($request->has('language_scope') && $request->language_scope != 'Semua Bahasa') {
            $query->where('language_code', $request->language_scope);
        }

        $currentUser = auth()->user();
        if ($currentUser->role === 'admin') {
            $query->where('school_origin', $currentUser->school_origin);
        }

        $materials = $query->get();

        return view('admin.materials', compact('materials', 'languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_indo' => 'required|string|max:255',
            'category' => 'required|in:literasi,numerasi,budaya',
            'level_difficulty' => 'required|integer|min:1|max:3',
            'language_code' => 'required',
            'json_file' => 'required|file|mimes:json,txt|max:2048', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = [
            'school_origin' => auth()->user()->school_origin,
            'title_indo' => $request->title_indo,
            'category' => $request->category,
            'level_difficulty' => $request->level_difficulty,
            'language_code' => $request->language_code,
        ];

        $jsonDebug = "File tidak diupload";

        // Proses JSON
        if ($request->hasFile('json_file')) {
            $file = $request->file('json_file');
            $jsonContent = file_get_contents($file->getRealPath());
            $decoded = json_decode($jsonContent, true);
            
            if (!isset($decoded['metadata']) || !isset($decoded['knowledge_map'])) {
            return back()->withErrors(['json_file' => 'Struktur JSON tidak memenuhi standar Metadata-First (Missing knowledge_map).']);
            }

            $jsonDebug = "✅ JSON Valid (" . count($decoded) . " blok konten)";
            $data['content_indo'] = $decoded;
        }

        $pythonScript = base_path('python/ai_processor.py');
        $pythonBinary = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
        $isScriptExists = file_exists($pythonScript) ? "✅ Ditemukan" : "❌ TIDAK DITEMUKAN";

        // dd([
        //     '🔹 1. Data Input Form' => $request->all(),
        //     '🔹 2. Status Upload Gambar' => $request->hasFile('image') ? 'Ada Gambar' : 'Tidak Ada',
        //     '🔹 3. Analisa JSON' => [
        //         'Status' => $jsonDebug,
        //         'Sample Data (Awal)' => $decoded ? array_slice($decoded, 0, 1) : null
        //     ],
        //     '🔹 4. Konfigurasi Backend AI' => [
        //         'Operating System' => PHP_OS,
        //         'Python Binary' => $pythonBinary,
        //         'Path Script Python' => $pythonScript,
        //         'Status File Script' => $isScriptExists
        //     ],
        //     '🔹 5. Estimasi Command CLI' => sprintf('start /B %s "%s" [ID_MATERI]', $pythonBinary, $pythonScript)
        // ]);

        // Upload Gambar
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('materials', 'public');
            $data['image_url'] = $path;
        }

        // Simpan ke database
        $material = Material::create($data);

        // ✅ TRIGGER PYTHON SCRIPT OTOMATIS
        $this->processAI($material->id);

        return redirect()->route('materials.index')
            ->with('success', 'Materi berhasil ditambahkan! AI sedang diproses di background.');
    }

    /**
     * ✅ FUNGSI BARU: TRIGGER PYTHON
     */
    /**
     * ✅ FUNGSI BARU: TRIGGER PYTHON
     */
    private function processAI($materialId)
    {
        try {
            $pythonScript = base_path('python/ai_processor.py');
            
            if (!file_exists($pythonScript)) {
                Log::error("Skrip tidak ditemukan di: " . $pythonScript);
                return;
            }

            $pythonBinary = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';

            // Eksekusi aman non-blocking menggunakan native queue
            dispatch(function() use ($pythonBinary, $pythonScript, $materialId) {
                $command = sprintf('"%s" "%s" %d', $pythonBinary, $pythonScript, $materialId);
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
        $material = Material::findOrFail($id);
        $material->update(['ai_status' => 'pending', 'ai_embeddings' => null]);
        
        $pythonScript = base_path('python/ai_processor.py');
        $pythonBinary = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';

        dispatch(function() use ($pythonBinary, $pythonScript, $id) {
            $command = sprintf('"%s" "%s" %d', $pythonBinary, $pythonScript, $id);
            exec($command . ' 2>&1', $output);
            Log::info("AI Regenerate result for ID {$id}: \n" . implode("\n", $output));
        })->afterResponse();
        
        return response()->json(['status' => 'success', 'message' => 'Regenerasi dimulai']);
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'title_indo' => 'required|string|max:255',
            'category' => 'required',
            'level_difficulty' => 'required|integer',
            'language_code' => 'required',
            'json_file' => 'nullable|file|mimes:json,txt|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $material->title_indo = $request->title_indo;
        $material->category = $request->category;
        $material->level_difficulty = $request->level_difficulty;
        $material->language_code = $request->language_code;

        // Cek Ganti JSON
        if ($request->hasFile('json_file')) {
            $file = $request->file('json_file');
            $jsonContent = file_get_contents($file->getRealPath());
            $decoded = json_decode($jsonContent, true);

            if ($decoded === null) {
                return back()->withErrors(['json_file' => 'File JSON baru tidak valid.']);
            }

            $material->content_indo = $decoded;
            
            // ✅ TRIGGER AI LAGI KARENA KONTEN BERUBAH
            $material->ai_status = 'pending';
        }

        // Cek Ganti Gambar
        if ($request->hasFile('image')) {
            if ($material->image_url && Storage::disk('public')->exists($material->image_url)) {
                Storage::disk('public')->delete($material->image_url);
            }
            
            $path = $request->file('image')->store('materials', 'public');
            $material->image_url = $path;
        }

        $material->save();

        // ✅ TRIGGER PYTHON JIKA KONTEN BERUBAH
        if ($request->hasFile('json_file')) {
            $this->processAI($material->id);
        }

        return redirect()->route('materials.index')->with('success', 'Data materi diperbarui!');
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'Materi berhasil dihapus.');
    }
}