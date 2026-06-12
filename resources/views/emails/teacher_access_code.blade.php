<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode Akses Guru NusaLearn</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F5F3FF;
            color: #000;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 2px solid #000;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 4px 4px 0 #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #7C3AED;
            margin: 0;
            font-size: 24px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
        }
        .kode-box {
            background-color: #EDE9FE;
            border: 2px solid #7C3AED;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #4C1D95;
            margin: 20px 0;
            border-radius: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NusaLearn</h1>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $teacher->nama }}</strong>,</p>
            <p>Akun guru Anda telah diverifikasi oleh Administrator. Berikut adalah Kode Akses unik Anda untuk masuk ke sistem NusaLearn:</p>
            
            <div class="kode-box">
                {{ $accessCode }}
            </div>
            
            <p>Gunakan kode akses ini bersama dengan kredensial login Anda. Jangan berikan kode ini kepada siapa pun untuk menjaga keamanan akun Anda.</p>
            
            <p>Terima kasih,<br>Tim Administrator NusaLearn</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} NusaLearn Management System. All rights reserved.
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
