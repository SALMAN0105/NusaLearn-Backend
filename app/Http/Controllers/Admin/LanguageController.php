<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::latest()->get();
        return view('admin.languages', compact('languages'));
    }

    /**
     * 🚀 STORE: Upload + Auto Convert Langsung
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:20|unique:languages,code',
            'dataset' => 'required|file|mimes:json,txt|max:5120', // Max 5MB
        ]);

        try {
            $file = $request->file('dataset');
            $code = strtolower($validated['code']);

            // 1️⃣ BACA & DECODE JSON
            $jsonContent = file_get_contents($file->getRealPath());
            $decoded = json_decode($jsonContent, true);

            if ($decoded === null) {
                return back()->withErrors(['dataset' => 'File JSON tidak valid atau rusak!']);
            }

            // 2️⃣ AUTO-CONVERT KE FORMAT FLAT
            $convertedData = $this->convertToFlatFormat($decoded);

            // 3️⃣ SIMPAN KE STORAGE dengan nama standar
            $fileName = "dictionaries/kamus_{$code}.json";
            
            Storage::disk('public')->put(
                $fileName,
                json_encode($convertedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            // 4️⃣ SIMPAN KE DATABASE
            Language::create([
                'name' => $validated['name'],
                'code' => $code,
                'json_file' => $fileName,
                'version_hash' => md5(json_encode($convertedData)), // Hash untuk detect perubahan
                'is_active' => true,
            ]);

            return back()->with('success', "Bahasa {$validated['name']} berhasil ditambahkan! (Total: " . count($convertedData) . " kata)");

        } catch (\Exception $e) {
            return back()->withErrors(['dataset' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
 * ✏️ UPDATE: Edit nama, kode, dan/atau ganti dataset
 */
public function update(Request $request, Language $language)
{
    $validated = $request->validate([
        'name'    => 'required|string|max:50',
        'code'    => 'required|string|max:20|unique:languages,code,' . $language->id,
        'dataset' => 'nullable|file|mimes:json,txt|max:5120',
    ]);

    try {
        $code = strtolower($validated['code']);

        // Jika ada file dataset baru, proses dan ganti yang lama
        if ($request->hasFile('dataset')) {
            $file = $request->file('dataset');
            $jsonContent = file_get_contents($file->getRealPath());
            $decoded = json_decode($jsonContent, true);

            if ($decoded === null) {
                return back()->withErrors(['dataset' => 'File JSON tidak valid atau rusak!']);
            }

            $convertedData = $this->convertToFlatFormat($decoded);

            // Hapus file lama jika ada
            if ($language->json_file && Storage::disk('public')->exists($language->json_file)) {
                Storage::disk('public')->delete($language->json_file);
            }

            $fileName = "dictionaries/kamus_{$code}.json";
            Storage::disk('public')->put(
                $fileName,
                json_encode($convertedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $language->json_file     = $fileName;
            $language->version_hash  = md5(json_encode($convertedData));
        }

        $language->name = $validated['name'];
        $language->code = $code;
        $language->save();

        return back()->with('success', "Bahasa {$validated['name']} berhasil diperbarui!");

    } catch (\Exception $e) {
        return back()->withErrors(['dataset' => 'Error: ' . $e->getMessage()]);
    }
}

    /**
     * 🔄 LOGIC KONVERSI UNIVERSAL
     */
    private function convertToFlatFormat($data)
    {
        $result = [];

        // FORMAT 1: Array of Objects [{"source_text": "x", "target_text": "y"}]
        if (is_array($data) && isset($data[0]['source_text'])) {
            foreach ($data as $item) {
                $source = strtolower(trim($item['source_text'] ?? ''));
                $target = trim($item['target_text'] ?? '');
                if ($source && $target) {
                    $result[$source] = $target;
                }
            }
            return $result;
        }

        // FORMAT 2: Array of Objects dengan key lain (flexible)
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                $source = $item['source_text'] ?? $item['word'] ?? $item['indo'] ?? $item['source'] ?? null;
                $target = $item['target_text'] ?? $item['translation'] ?? $item['tolaki'] ?? $item['target'] ?? null;

                if ($source && $target) {
                    $result[strtolower(trim($source))] = trim($target);
                }
            }
            return $result;
        }

        // FORMAT 3: Sudah Object {"kata": "terjemahan"}
        if (is_array($data) && !isset($data[0])) {
            foreach ($data as $key => $value) {
                $result[strtolower(trim($key))] = trim($value);
            }
            return $result;
        }

        return $data; // Fallback
    }

    /**
     * 🗑️ DELETE: Hapus file juga
     */
    public function destroy(Language $language)
    {
        if ($language->json_file && Storage::disk('public')->exists($language->json_file)) {
            Storage::disk('public')->delete($language->json_file);
        }

        $language->delete();
        return back()->with('success', 'Bahasa berhasil dihapus.');
    }
}