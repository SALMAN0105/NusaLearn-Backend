<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode Verifikasi NusalearnApp</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 40px 0;">
    <div style="max-w-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        
        <div style="background-color: #059669; padding: 30px 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; letter-spacing: 1px;">Nusaleran<span style="color: #a7f3d0;">App</span></h1>
            <p style="color: #d1fae5; margin: 5px 0 0 0; font-size: 14px;">Enterprise Security System</p>
        </div>

        <div style="padding: 30px 40px; color: #334155;">
            <h2 style="font-size: 18px; margin-top: 0;">Permintaan Reset Kredensial</h2>
            <p style="font-size: 15px; line-height: 1.6; color: #475569;">
                Halo! Saya developer <strong>Nusalearn</strong>, ini kode verifikasi mu untuk melakukan reset kata_sandi pada portal Guru NusalearnApp.
            </p>
            
            <div style="background-color: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 20px; text-align: center; margin: 25px 0;">
                <span style="font-family: monospace; font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #0f172a;">{{ $otp }}</span>
            </div>
            
            <p style="font-size: 13px; color: #ef4444; background-color: #fef2f2; padding: 10px; border-radius: 6px;">
                <strong>Peringatan Keamanan:</strong> Kode ini hanya berlaku selama 10 Menit. Jangan berikan kode ini kepada siapapun, termasuk pihak Nusalearn.
            </p>
        </div>

        <div style="background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="font-size: 12px; color: #94a3b8; margin: 0;">&copy; {{ date('Y') }} Nusalearn Learning Management System.<br>Generated automatically by System.</p>
        </div>
    </div>
<!-- SweetAlert2 Neo-Brutalism -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const neoSwal = Swal.mixin({
        customClass: {
            popup: 'border-2 border-black rounded-2xl shadow-neo-lg bg-white text-black',
            title: 'font-black uppercase tracking-tight text-xl',
            confirmButton: 'bg-neo-green border-2 border-black text-black font-black uppercase rounded-lg px-6 py-2 shadow-neo-sm hover:-translate-y-1 hover:shadow-neo transition-all',
            htmlContainer: 'font-bold text-sm text-gray-700'
        },
        buttonsStyling: false
    });

    window.showCustomAlert = function(msg) {
        neoSwal.fire({ icon: 'warning', title: 'Perhatian!', text: msg });
    };

    @if(session('success'))
        neoSwal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session("success") }}' });
    @endif

    @if($errors->any())
        neoSwal.fire({ icon: 'error', title: 'Oops...', text: '{{ $errors->first() }}' });
    @endif
</script>
</body>
</html>
