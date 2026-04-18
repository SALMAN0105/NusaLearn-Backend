bagian -3 (konteks nya biar kamu paham)

# Arsitektur Sistem Kuis Multimedia Interaktif - NusaLearn

**Status:** Draft Arsitektur Final  
**Arsitek:** Principal Distinguished Engineer  
**Teknologi:** Laravel (Backend), Flutter (Mobile), AI (Content Generator)

## 1. Analisis Permasalahan (Problem Statement)
Sistem kuis saat ini terbatas pada format Pilihan Ganda (Multiple Choice). Terdapat kebutuhan untuk meningkatkan variasi soal menjadi multimedia interaktif (Drag & Drop, Teka-teki, dll) dengan kendala teknis sebagai berikut:
1. **Skalabilitas Konten:** Guru/Admin membutuhkan cara cepat untuk membuat soal variatif tanpa harus mendesain aset satu per satu.
2. **Manajemen Aset:** Penyimpanan data biner (gambar/audio) di dalam database (SQLite) menyebabkan degradasi performa dan ukuran database yang membengkak.
3. **Integritas AI:** Risiko AI melakukan halusinasi terhadap nama file aset yang tidak eksis di server.
4. **Efisiensi Sinkronisasi:** Memastikan siswa dapat mengerjakan soal secara offline tanpa mengunduh aset yang redundan.

## 2. Solusi Arsitektural: Server-Driven UI & JSON Schema
Solusi yang disepakati adalah memisahkan **Logika Soal (JSON)** dari **Aset Biner (File)**. AI bertugas menyusun skenario soal dalam format JSON, sementara aplikasi mobile bertugas merender UI berdasarkan template yang dipilih.

### Langkah demi Langkah Implementasi

#### Tahap 1: Pengadaan Aset Otomatis (Backend - Laravel)
Sistem tidak lagi mengandalkan input manual, melainkan menggunakan pipa pasokan aset dari API pihak ketiga:
1. **Integrasi API:** Menghubungkan Laravel ke Pixabay (Vektor/Ilustrasi), Pexels (Foto/Video), The Noun Project (Ikon), dan Freesound (Audio).
2. **Content-Addressable Storage (CAS):** - Aset diunduh ke server dan diberi nama berdasarkan *hash* (MD5/SHA1).
   - Menyimpan metadata aset di tabel `asset_libraries` (nama_file, tag, sumber_api).
3. **Manifest Generation:** Membuat fungsi untuk memindai folder aset guna memberikan "konteks" kepada AI tentang file apa saja yang tersedia.

#### Tahap 2: Orkestrasi AI (AI Engine)
Menggunakan LLM untuk membangkitkan konten soal dengan kontrol ketat:
1. **Manifest-Based Prompting:** Mengirimkan daftar file aset yang tersedia ke AI agar AI hanya menggunakan referensi file yang benar-benar ada.
2. **Deterministic Output:** Memaksa AI memberikan output JSON murni yang divalidasi oleh Laravel sebelum disimpan ke database.
3. **Template Selection:** Admin memilih template (misal: "Matching Game"), dan AI mengisi kontennya (Pertanyaan, Jawaban, Koordinat/Logika).

#### Tahap 3: Sinkronisasi & Penyimpanan Lokal (Mobile - Flutter)
Mengoptimalkan performa perangkat siswa:
1. **Atomic Sync:**
   - Aplikasi mengunduh JSON kuis.
   - Aplikasi memindai field `assets_required`.
   - Melakukan pengecekan: Jika file belum ada di penyimpanan internal HP, aplikasi mengunduh dari server.
2. **Sandboxing Aset:**
   - Menyimpan aset di `getApplicationDocumentsDirectory()`, bukan di SQLite.
   - Menggunakan `path_provider` dan `dio` untuk manajemen file.
3. **Defensive Rendering:**
   - Menggunakan Factory Pattern untuk menampilkan widget berdasarkan `template_type`.
   - Melakukan verifikasi keberadaan file sebelum kuis dimulai untuk mencegah *crash*.

## 3. Rekomendasi API Pihak Ketiga
PIXABAY_IMAGE_API_KEY=55430786-dbda2b8d63f54675f348d208d

PIXABAY_VIDEO_API_KEY=55430786-dbda2b8d63f54675f348d208d

PIXELS_API_KEY=DuWxAHeIvOp950rQUHE5ETj0yjakqfhGFCcnRkS7WlEMrbVVo3udODWh

FREESOUNND_API_KEY=IjOUwGfwuTDXqk9tEJWNyOMnl0SytG1By5ES0skh
FREESOUND_CLIENT_ID=rcxaELAaF0lXdDPgkuq7

FREEPIK_API_KEY=FPSX9cc9875dab82fc1b41924d410fa5bfd6

Iconify API
Cara Penggunaan: Anda cukup melakukan HTTP GET request ke endpoint publik mereka:
https://api.iconify.design/mdi/home.svg (Mengembalikan file SVG langsung)

Atribusi (Wajib): Pexels mewajibkan Anda untuk menampilkan tautan atribusi ke fotografer dan Pexels di UI aplikasi Anda.

WAJIB PAKE INI
http://freesound.org/home/app_permissions/permission_granted/.

saya juga ingin menngunakan LottieFiles


## 4. Prinsip Pengembangan (Guiding Principles)
1. **Open/Closed Principle:** Sistem harus mudah ditambah template kuis baru tanpa merubah kode utama di Backend.
2. **Separation of Concerns:** AI fokus pada konten, Laravel pada manajemen data, dan Flutter pada pengalaman pengguna.
3. **Data Integrity:** Tidak boleh ada file yang dirujuk dalam JSON yang tidak tersedia di folder aset fisik.

*Dokumen ini merupakan panduan teknis resmi untuk pengembangan fitur Multimedia Interaktif pada proyek NusaLearn.*

note: bagian - 4 adalah pertanyaan sebenarnya, ini biar kamu mengerti koteksnya (maka dari itu saya tidak berikan code dari fitur lain karena saya ingin fokus ke fitur ini)