<?php
// app/Http/Controllers/Admin/AssetController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Models\PustakaAset;
use App\Services\AssetAcquisitionService;
use App\Services\ManifestGeneratorService;

class AssetController extends Controller
{
    public function __construct(
        private AssetAcquisitionService  $acquisitionService,
        private ManifestGeneratorService $manifestService,
    ) {}

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 1: CRUD VIEWS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * GET /admin/assets
     * Tampilkan daftar semua aset di Asset Library.
     */
    public function index(Request $request)
    {
        $query = PustakaAset::query();

        // Filter by type — FIX: pakai mime_type karena kolom asset_type tidak ada
        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'lottie') {
                $query->where('tipe_mime', 'application/json')
                      ->where('sumber_api', 'lottiefiles');
            } elseif ($type === 'icon') {
                $query->where('sumber_api', 'iconify');
            } else {
                // image, audio, video → pakai prefix mime_type
                $query->where('tipe_mime', 'like', $type . '/%');
            }
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('sumber_api', $request->source);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('aktif', $request->status === 'active');
        }

        // Search by tags atau original_name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_asli', 'like', "%{$search}%")
                  ->orWhereJsonContains('tag', $search);
            });
        }

        $assets = $query->orderBy('dibuat_pada', 'desc')->paginate(5);

        // Statistik untuk dashboard
        // FIX: pakai mime_type untuk menghitung per tipe, bukan kolom asset_type
        // FIX: pakai size_kb bukan file_kb (sesuai kolom di model/migration)
        $stats = [
            'total'  => PustakaAset::count(),
            'images' => PustakaAset::where('tipe_mime', 'like', 'image/%')->count(),
            'audio'  => PustakaAset::where('tipe_mime', 'like', 'audio/%')->count(),
            'lottie' => PustakaAset::where('tipe_mime', 'application/json')
                            ->where('sumber_api', 'lottiefiles')->count(),
            'icons'  => PustakaAset::where('sumber_api', 'iconify')->count(),
            'active' => PustakaAset::where('aktif', true)->count(),
            // Total ukuran file dalam MB — FIX: size_kb bukan file_kb
            'total_size_mb' => round(
                PustakaAset::sum('ukuran_kb') / 1024,
                2
            ),
        ];

        return view('admin.assets.index', compact('assets', 'stats'));
    }

    /**
     * POST /admin/assets
     * Upload aset manual dari komputer admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file'       => 'required|file|max:10240', // max 10MB
            'tipe_aset' => 'required|in:image,audio,lottie,icon,video',
            'tag' => 'nullable|string',
        ]);

        try {
            $file      = $request->file('file');
            $hash      = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $filename  = $hash . '.' . $extension;

            // Cek duplikat (CAS principle)
            $existing = PustakaAset::where('nama_file', $filename)->first();
            if ($existing) {
                return back()->with('info', "Aset sudah ada: {$filename}");
            }

            // Simpan file
            $file->storeAs('quiz-assets', $filename, 'public');

            // Parse tags
            $tags = [];
            if ($request->filled('tags')) {
                $tags = array_map('trim', explode(',', $request->tag));
                $tags = array_filter($tags);
            }

            // Simpan ke database — FIX: pakai size_kb (bukan file_kb)
            PustakaAset::create([
                'nama_file'   => $filename,
                'nama_asli'   => $file->getClientOriginalName(),
                'tipe_mime'   => $file->getMimeType(),
                'ekstensi'    => $extension,
                'tipe_aset'   => $request->tipe_aset,
                'ukuran_kb'   => round($file->getSize() / 1024),
                'sumber_api'  => 'manual_upload',
                'tag'         => json_encode($tags),
                'aktif'       => true,
            ]);

            return back()->with('success', "Aset berhasil diupload: {$filename}");

        } catch (\Exception $e) {
            Log::error('[AssetController@store] ' . $e->getMessage());
            return back()->with('error', 'Gagal mengupload aset: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 2: API SEARCH & FETCH
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /admin/assets/search-api
     * Body: { "source": "pixabay", "query": "rumah adat", "type": "image" }
     *
     * Mencari aset dari API pihak ketiga tanpa mengunduhnya.
     * Admin bisa preview dulu sebelum memutuskan untuk fetch.
     */
    public function searchApi(Request $request)
    {
        $request->validate([
            'source' => 'required|in:pixabay,pexels,freesound,freepik,iconify,lottiefiles',
            'query'  => 'required|string|min:2|max:100',
            'type'   => 'nullable|in:image,audio,video,icon,lottie',
            'limit'  => 'nullable|integer|min:1|max:30',
        ]);

        try {
            $results = $this->acquisitionService->search(
                source: $request->input('source'),
                query:  $request->input('query'),
                type:   $request->input('type', 'image'),
                limit:  $request->input('limit', 12),
            );

            return response()->json([
                'status'  => 'success',
                'data'    => $results,
                'meta'    => [
                    'source' => $request->source,
                    'query'  => $request->input('query'),
                    'count'  => count($results),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('[AssetController@searchApi] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mencari aset: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /admin/assets/fetch-api
     * Body: { "source": "pixabay", "external_id": "123456", "url": "...", "tags": [...] }
     *
     * Mengunduh aset dari URL eksternal ke server lokal.
     * Menggunakan CAS: jika konten sama, file tidak diduplikasi.
     */
    public function fetchFromApi(Request $request)
    {
        $request->validate([
            'source'      => 'required|in:pixabay,pexels,freesound,freepik,iconify,lottiefiles',
            'external_id' => 'nullable|string|max:255',
            'url'         => 'required|url',
            'tipe_aset' => 'required|in:image,audio,video,icon,lottie',
            'tag' => 'nullable|array',
            'tags.*'      => 'string|max:50',
            'nama_asli' => 'nullable|string|max:255',
        ]);

        try {
            $asset = $this->acquisitionService->fetchAndStore(
                url:          $request->url,
                source:       $request->source,
                assetType:    $request->tipe_aset,
                tags:         $request->input('tags', []),
                externalId:   $request->external_id,
                originalName: $request->nama_asli,
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Aset berhasil diunduh.',
                'data'    => [
                    'id'         => $asset->id,
                    'nama_file' => $asset->nama_file,
                    'tipe_mime' => $asset->tipe_mime,
                    // FIX: pakai size_kb bukan file_kb
                    'ukuran_kb' => $asset->ukuran_kb,
                    'url'        => route('assets.serve', ['hash' => $asset->nama_file]),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('[AssetController@fetchFromApi] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengunduh aset: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 3: MANIFEST & UTILITY
    // ═══════════════════════════════════════════════════════════════════

    /**
     * GET /admin/assets/manifest
     * Tampilkan manifest aset untuk debugging.
     * Manifest ini yang dikirim ke AI saat generate soal.
     */
    public function showManifest()
    {
        try {
            $manifest = $this->manifestService->generate();

            return response()->json([
                'status'       => 'success',
                'generated_at' => now()->toISOString(),
                'summary'      => [
                    'total_assets' => array_sum(array_map('count', $manifest)),
                    'by_type'      => array_map('count', $manifest),
                ],
                'manifest'     => $manifest,
            ]);

        } catch (\Exception $e) {
            Log::error('[AssetController@showManifest] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal generate manifest.',
            ], 500);
        }
    }

    /**
     * PATCH /admin/assets/{asset}/toggle
     * Toggle status aktif/nonaktif aset.
     * Aset nonaktif tidak akan muncul di manifest dan tidak bisa diakses Flutter.
     */
    public function toggleActive(PustakaAset $asset)
    {
        $user = auth()->user();
        if ($user && $user->peran === 'guru') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk mengubah status aset global.',
            ], 403);
        }

        try {
            $asset->update(['aktif' => !$asset->aktif]);

            $status = $asset->aktif ? 'diaktifkan' : 'dinonaktifkan';

            return response()->json([
                'status'    => 'success',
                'message'   => "Aset berhasil {$status}.",
                'aktif' => $asset->aktif,
            ]);

        } catch (\Exception $e) {
            Log::error('[AssetController@toggleActive] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah status aset.',
            ], 500);
        }
    }

    /**
     * DELETE /admin/assets/{asset}
     * Hapus aset dari database DAN dari disk.
     * Cek dulu apakah aset sedang digunakan oleh soal aktif.
     */
    public function destroy(PustakaAset $asset)
    {
        $user = auth()->user();
        if ($user && $user->peran === 'guru') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menghapus aset global.',
            ], 403);
        }

        try {
            // Cek apakah aset sedang digunakan oleh soal aktif
            $usedByQuestions = \App\Models\Soal::where('aktif', true)
                ->where(function ($q) use ($asset) {
                    $q->whereJsonContains('aset_diperlukan', $asset->nama_file)
                      ->orWhere('data_soal', 'like', "%{$asset->nama_file}%");
                })
                ->count();

            if ($usedByQuestions > 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Aset tidak bisa dihapus karena digunakan oleh {$usedByQuestions} soal aktif.",
                    'hint'    => 'Nonaktifkan soal terkait terlebih dahulu, atau gunakan toggle untuk menonaktifkan aset.',
                ], 409); // 409 Conflict
            }

            // Hapus file fisik dari disk
            $filePath = 'quiz-assets/' . $asset->nama_file;
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // Hapus record dari database
            $filename = $asset->nama_file;
            $asset->delete();

            return response()->json([
                'status'  => 'success',
                'message' => "Aset {$filename} berhasil dihapus.",
            ]);

        } catch (\Exception $e) {
            Log::error('[AssetController@destroy] ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus aset: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 4: FILE SERVING
    // ═══════════════════════════════════════════════════════════════════

    /**
     * GET /assets/serve/{hash}
     * Melayani file aset ke Flutter dengan header yang optimal.
     *
     * Keunggulan vs Storage::url() langsung:
     * 1. Bisa tambah auth check di masa depan
     * 2. Cache-Control: immutable (CAS = konten tidak pernah berubah)
     * 3. Proper MIME type dari database
     * 4. Hit/miss logging untuk analytics
     * 5. Bisa serve dari disk manapun (local, S3, dll) tanpa ubah URL Flutter
     */
    public function serveFile(string $path)
    {
        try {
            $asset = PustakaAset::where('nama_file', $path)
                ->where('aktif', true)
                ->first();

            if (!$asset) {
                return response()->json(['status' => 'error', 'message' => 'Aset tidak ditemukan.'], 404);
            }

            // Cek apakah pakai path baru (assets/) atau path lama (quiz-assets/)
            $filePath = str_starts_with($path, 'assets/') ? $path : 'quiz-assets/' . $path;
            
            if (!Storage::disk('public')->exists($filePath)) {
                abort(404);
            }

            $fileContent = Storage::disk('public')->get($filePath);
            $mimeType    = $asset->tipe_mime ?? $this->guessMimeType($path);
            $sizeKb      = $asset->ukuran_kb ?? strlen($fileContent);

            return Response::make($fileContent, 200, [
                'Content-Type'        => $mimeType,
                'Content-Length'      => $sizeKb,
                'Cache-Control'       => 'public, max-age=31536000, immutable',
                'ETag'                => '"' . md5($path) . '"',
                'Last-Modified'       => $asset->dibuat_pada->toRfc7231String(),
                'Access-Control-Allow-Origin' => '*',
                'Content-Disposition' => 'inline; filename="' . $asset->nama_asli . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('[AssetController@serveFile] ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 5: PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Tebak MIME type dari ekstensi file.
     * Sama dengan helper di SyncController — bisa di-extract ke Trait
     * jika diperlukan di masa depan.
     */
    private function guessMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match($extension) {
            // Images
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',

            // Audio
            'mp3'         => 'audio/mpeg',
            'ogg'         => 'audio/ogg',
            'wav'         => 'audio/wav',
            'flac'        => 'audio/flac',

            // Video
            'mp4'         => 'video/mp4',
            'webm'        => 'video/webm',

            // Animation
            'json'        => 'application/json',
            'lottie'      => 'application/json',

            default       => 'application/octet-stream',
        };
    }
}
