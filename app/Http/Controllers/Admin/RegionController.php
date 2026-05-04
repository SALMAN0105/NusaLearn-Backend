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
        // 1. Validasi Awal
        // Jika tidak ada upload_file, maka input manual wajib diisi.
        // Jika ada upload_file, input manual opsional.
        $request->validate([
            'upload_file'   => 'nullable|file|mimes:csv,json,txt|max:2048',
            'postal_code'   => 'required_without:upload_file|nullable|string|max:10|unique:regions,postal_code',
            'district_name' => 'required_without:upload_file|nullable|string|max:100',
            'language_code' => 'required_without:upload_file|nullable|exists:languages,code',
        ]);

        // 2. Logika Batch Import
        if ($request->hasFile('upload_file')) {
            $file = $request->file('upload_file');
            $extension = $file->getClientOriginalExtension();
            $dataToImport = [];

            try {
                if ($extension === 'csv' || $file->getClientMimeType() === 'text/plain') {
                    $handle = fopen($file->getRealPath(), 'r');
                    $header = fgetcsv($handle, 1000, ','); // Ambil header: postal_code, district_name, language_code
                    
                    while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        if (count($row) < 3) continue; // Skip baris tidak lengkap
                        $dataToImport[] = [
                            'postal_code'   => trim($row[0]),
                            'district_name' => trim($row[1]),
                            'language_code' => trim($row[2]),
                        ];
                    }
                    fclose($handle);
                } elseif ($extension === 'json') {
                    $jsonContent = json_decode(file_get_contents($file->getRealPath()), true);
                    if (is_array($jsonContent)) {
                        $dataToImport = $jsonContent;
                    }
                }

                if (empty($dataToImport)) {
                    return back()->withErrors(['upload_file' => 'File kosong atau format tidak sesuai.']);
                }

                // 3. Eksekusi Database dengan Transaksi & Validasi Baris
                $importedCount = 0;
                $activeLanguages = \App\Models\Language::pluck('code')->toArray();

                \Illuminate\Support\Facades\DB::transaction(function () use ($dataToImport, $activeLanguages, &$importedCount) {
                    foreach ($dataToImport as $item) {
                        // Defensive checks per baris
                        $pCode = $item['postal_code'] ?? null;
                        $dName = $item['district_name'] ?? null;
                        $lCode = $item['language_code'] ?? null;

                        if (!$pCode || !$dName || !$lCode) continue;
                        
                        // Validasi keberadaan bahasa
                        if (!in_array($lCode, $activeLanguages)) continue;

                        // Validasi uniknya kode pos (Skip jika sudah ada)
                        if (Region::where('postal_code', $pCode)->exists()) continue;

                        Region::create([
                            'postal_code'   => $pCode,
                            'district_name' => $dName,
                            'language_code' => $lCode,
                        ]);
                        $importedCount++;
                    }
                });

                return back()->with('success', "Berhasil mengimpor {$importedCount} wilayah baru.");

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('[RegionImport] Error: ' . $e->getMessage());
                return back()->withErrors(['upload_file' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage()]);
            }
        }

        // 4. Logika Registrasi Manual Tunggal
        Region::create([
            'postal_code'   => $request->postal_code,
            'district_name' => $request->district_name,
            'language_code' => $request->language_code,
        ]);

        return back()->with('success', 'Wilayah baru berhasil dipetakan secara manual.');
    }
        public function update(Request $request, Region $region)
    {
        $validated = $request->validate([
            'postal_code'   => 'required|string|max:10|unique:regions,postal_code,' . $region->id,
            'district_name' => 'required|string|max:100',
            'language_code' => 'required|exists:languages,code',
        ]);
 
        $region->update($validated);
 
        return back()->with('success', 'Data wilayah berhasil diperbarui.');
    }

    public function destroy(Region $region)
    {
        $region->delete();
        return back()->with('success', 'Data wilayah dihapus.');
    }
}