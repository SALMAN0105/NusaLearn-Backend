<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register Administrator - NusaLearn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --purple:     #7C3AED;
            --purple-mid: #8B5CF6;
            --purple-lt:  #EDE9FE;
            --purple-dark:#4C1D95;
            --black:      #0A0A0A;
            --white:      #FFFFFF;
            --gray-soft:  #F5F3FF;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            background: var(--white);
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            display: flex; align-items: center; justify-content: center;
            position: relative; padding: 32px 0;
        }

        /* PRELOADER */
        #preloader { position: fixed; inset: 0; background: var(--white); z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity .6s ease, visibility .6s ease; }
        .pre-logo { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 2.5rem; color: var(--black); letter-spacing: -2px; display: flex; align-items: center; gap: 6px; animation: pre-pulse 1.2s ease-in-out infinite; }
        .pre-logo span { color: var(--purple); }
        @keyframes pre-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* BG SCENE */
        .bg-scene { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .blob-tl { position: absolute; top: -120px; left: -100px; width: 520px; height: 520px; background: var(--purple); border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%; opacity: .09; animation: morph-a 12s ease-in-out infinite; }
        .blob-br { position: absolute; bottom: -100px; right: -80px; width: 440px; height: 440px; background: var(--purple-mid); border-radius: 40% 60% 30% 70% / 60% 40% 50% 50%; opacity: .07; animation: morph-b 14s ease-in-out infinite; }
        .dot-grid { position: absolute; inset: 0; background-image: radial-gradient(circle, #7C3AED22 1px, transparent 1px); background-size: 28px 28px; }
        .deco-ring { position: absolute; bottom: 60px; right: 80px; width: 90px; height: 90px; border: 11px solid var(--purple); border-radius: 50%; opacity: .14; animation: spin-slow 20s linear infinite; }
        .deco-tri { position: absolute; top: 10%; left: 5%; width: 0; height: 0; border-left: 32px solid transparent; border-right: 32px solid transparent; border-bottom: 55px solid var(--purple-lt); opacity: .7; animation: float-y 5s ease-in-out infinite; filter: drop-shadow(3px 3px 0 var(--black)); }
        .deco-star { position: absolute; top: 30%; right: 6%; font-size: 42px; line-height: 1; animation: spin-slow 15s linear infinite reverse; opacity: .22; color: var(--purple); }

        /* Badge akses kode */
        .float-badge { position: absolute; bottom: 22%; left: 5%; background: var(--purple); border: 2.5px solid var(--black); border-radius: 16px; box-shadow: 4px 4px 0 var(--black); padding: 10px 18px; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 12px; color: var(--white); display: flex; align-items: center; gap: 8px; animation: float-y 4.5s ease-in-out infinite .5s; white-space: nowrap; }

        @keyframes morph-a { 0%,100%{border-radius:60% 40% 70% 30%/50% 60% 40% 50%} 50%{border-radius:30% 70% 40% 60%/60% 30% 70% 40%} }
        @keyframes morph-b { 0%,100%{border-radius:40% 60% 30% 70%/60% 40% 50% 50%} 50%{border-radius:70% 30% 60% 40%/30% 70% 40% 60%} }
        @keyframes float-y { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }
        @keyframes spin-slow { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
        @keyframes slide-up { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:translateY(0)} }

        /* WRAP */
        .register-wrap { position: relative; z-index: 10; width: 100%; max-width: 640px; padding: 0 20px; }

        /* BRAND */
        .brand { text-align: center; margin-bottom: 28px; animation: slide-up .5s ease both; }
        .brand-icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: var(--purple); border: 3px solid var(--black); border-radius: 20px; box-shadow: 5px 5px 0 var(--black); margin-bottom: 14px; font-size: 26px; color: var(--white); transform: rotate(-6deg); transition: transform .3s; }
        .brand-icon:hover { transform: rotate(0deg); }
        .brand h1 { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 2.2rem; letter-spacing: -2px; color: var(--black); margin: 0 0 6px; line-height: 1; }
        .brand h1 span { color: var(--purple); }
        .brand p { font-size: 13px; font-weight: 600; color: #6B7280; margin: 0; }

        /* CARD */
        .card { background: var(--white); border: 2.5px solid var(--black); border-radius: 28px; box-shadow: 8px 8px 0 var(--black); padding: 36px 36px 32px; animation: slide-up .55s ease .08s both; }
        @media (max-width: 560px) { .card { padding: 22px 18px; } }

        .card-heading { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 1.45rem; letter-spacing: -0.5px; color: var(--black); margin: 0 0 4px; }
        .card-sub { font-size: 13px; font-weight: 500; color: #9CA3AF; margin: 0 0 24px; }

        /* ACCESS CODE BOX */
        .access-box { background: var(--purple-lt); border: 2.5px solid var(--purple); border-radius: 18px; padding: 20px; margin-bottom: 24px; box-shadow: 3px 3px 0 var(--purple-dark); }
        .access-box-label { font-size: 13px; font-weight: 800; color: var(--purple-dark); margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
        .access-note { font-size: 11px; font-weight: 600; color: var(--purple); margin-top: 8px; }

        /* DIVIDER */
        .section-divider { border: none; border-top: 2px dashed #E5E7EB; margin: 20px 0; }
        .section-title { font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 800; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .section-title::after { content:''; flex:1; height:2px; background:#F3F4F6; border-radius:99px; }

        /* GRID */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 560px) { .form-grid { grid-template-columns: 1fr; } }

        /* LABEL & INPUT */
        .field-label { display: block; font-size: 13px; font-weight: 700; color: var(--black); margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #A78BFA; font-size: 14px; transition: color .2s; pointer-events: none; }
        .input-wrap:focus-within .input-icon { color: var(--purple); }
        .field-input { width: 100%; background: var(--gray-soft); border: 2px solid #E5E7EB; border-radius: 12px; padding: 12px 16px 12px 42px; font-size: 13px; font-weight: 500; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--black); transition: border-color .2s, box-shadow .2s, background .2s; outline: none; }
        .field-input::placeholder { color: #C4B5FD; }
        .field-input:focus { border-color: var(--purple); background: var(--white); box-shadow: 0 0 0 3px #7C3AED15, 3px 3px 0 var(--purple); }
        .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #A78BFA; font-size: 13px; transition: color .2s; padding: 4px; }
        .toggle-pw:hover { color: var(--purple); }
        .field-group { margin-bottom: 0; }

        /* ERROR ALERT */
        .alert-error { background: #FFF1F2; border: 2px solid #F43F5E; border-radius: 14px; padding: 14px 16px; font-size: 13px; font-weight: 600; color: #BE123C; display: flex; align-items: flex-start; gap: 10px; margin-bottom: 22px; }
        .alert-error ul { margin: 4px 0 0; padding-left: 16px; font-weight: 500; font-size: 12px; }

        /* WARN BOX */
        .warn-box { background: #FFFBEB; border: 2px solid #F59E0B; border-radius: 12px; padding: 10px 14px; font-size: 12px; font-weight: 600; color: #92400E; margin-bottom: 14px; }

        /* BTN */
        .btn-submit { width: 100%; background: var(--purple); color: var(--white); border: 2.5px solid var(--black); border-radius: 14px; padding: 16px; font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800; letter-spacing: .3px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 4px 4px 0 var(--black); transition: transform .15s, box-shadow .15s, background .15s; outline: none; margin-top: 24px; }
        .btn-submit:hover { background: var(--purple-dark); transform: translate(-2px,-2px); box-shadow: 6px 6px 0 var(--black); }
        .btn-submit:active { transform: translate(2px,2px); box-shadow: 2px 2px 0 var(--black); }

        /* LOGIN LINK */
        .login-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--purple); text-decoration: none; padding: 8px 18px; border: 2px solid var(--purple-lt); border-radius: 99px; background: var(--purple-lt); transition: all .2s; }
        .login-link:hover { background: var(--purple); color: var(--white); border-color: var(--purple); transform: translateY(-1px); }

        /* FOOTER */
        .footer { text-align: center; margin-top: 24px; font-size: 12px; font-weight: 500; color: #9CA3AF; animation: slide-up .6s ease .18s both; }
    </style>
</head>
<body>

    <div id="preloader">
        <div class="pre-logo">Nusa<span>Learn</span> <span style="font-size:1.6rem;margin-left:4px">✦</span></div>
    </div>

    <div class="bg-scene">
        <div class="dot-grid"></div>
        <div class="blob-tl"></div>
        <div class="blob-br"></div>
        <div class="deco-ring"></div>
        <div class="deco-tri"></div>
        <div class="deco-star">✦</div>
        <div class="float-badge">
            <i class="fa-solid fa-lock" style="font-size:13px"></i>
            Akses Terbatas
        </div>
    </div>

    <div class="register-wrap">
        <div class="brand">
            <div class="brand-icon"><i class="fa-solid fa-user-plus"></i></div>
            <h1>Nusa<span>Learn</span></h1>
            <p>Registrasi Panel Administrator</p>
        </div>

        <div class="card">
            <p class="card-heading">Buat Akun Admin Baru 🔐</p>
            <p class="card-sub">Isi semua kolom di bawah untuk mendaftar sebagai administrator.</p>

            @if ($errors->any())
            <div class="alert-error">
                <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;flex-shrink:0"></i>
                <div>
                    <strong>Registrasi Gagal!</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.register.post') }}">
                @csrf

                {{-- Access Code --}}
                <div class="access-box">
                    <div class="access-box-label">
                        <i class="fa-solid fa-shield-halved"></i>
                        Kode Akses Pendaftaran <span style="color:#F43F5E">*</span>
                    </div>
                    <div style="position:relative">
                        <i class="fa-solid fa-shield-halved" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#A78BFA;font-size:14px;pointer-events:none"></i>
                        <input style="width:100%;background:#fff;border:2px solid var(--purple);border-radius:12px;padding:12px 16px 12px 42px;font-size:13px;font-weight:700;font-family:'Outfit',monospace;color:var(--black);letter-spacing:3px;text-transform:uppercase;outline:none;transition:box-shadow .2s" 
                               type="text" name="access_code" id="access_code" required placeholder="MASUKKAN TOKEN AKSES"
                               onfocus="this.style.boxShadow='3px 3px 0 var(--purple-dark)'" 
                               onblur="this.style.boxShadow='none'">
                    </div>
                    <p class="access-note"><i class="fa-solid fa-circle-info"></i> Registrasi ini terbatas. Masukkan kode akses institusi yang valid.</p>
                </div>

                {{-- Identitas --}}
                <div class="section-title"><i class="fa-solid fa-id-card" style="color:var(--purple)"></i> Identitas Administrator</div>
                <div class="form-grid" style="margin-bottom:14px">
                    <div class="field-group">
                        <label class="field-label">Nama Lengkap</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-id-card input-icon"></i>
                            <input class="field-input" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Budi Santoso">
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Username Akses</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user-shield input-icon"></i>
                            <input class="field-input" type="text" name="username" value="{{ old('username') }}" required placeholder="Contoh: admin_smk1">
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Asal Sekolah / Instansi</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-school input-icon"></i>
                            <input class="field-input" type="text" name="school_origin" value="{{ old('school_origin') }}" required placeholder="Contoh: SMKN 1 Kendari">
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Alamat Email Valid</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-envelope input-icon"></i>
                            <input class="field-input" type="email" name="email" value="{{ old('email') }}" required placeholder="admin@sekolah.sch.id">
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <hr class="section-divider">
                <div class="section-title"><i class="fa-solid fa-key" style="color:var(--purple)"></i> Keamanan Akun</div>
                <div class="warn-box"><i class="fa-solid fa-circle-exclamation"></i> Minimal 8 karakter, mengandung huruf besar, kecil, angka & simbol (@, #, dsb).</div>
                <div class="form-grid">
                    <div class="field-group">
                        <label class="field-label">Kata Sandi Baru</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-key input-icon"></i>
                            <input class="field-input" id="password" type="password" name="password" required placeholder="••••••••" style="padding-right:42px">
                            <button type="button" class="toggle-pw" onclick="togglePassword('password','eye-p')"><i id="eye-p" class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Konfirmasi Kata Sandi</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input class="field-input" id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••" style="padding-right:42px">
                            <button type="button" class="toggle-pw" onclick="togglePassword('password_confirmation','eye-c')"><i id="eye-c" class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>
                </div>

                <button class="btn-submit" type="submit">
                    <i class="fa-solid fa-user-plus"></i> Selesaikan Registrasi
                </button>

                <div style="text-align:center;margin-top:20px">
                    <a href="{{ route('login') }}" class="login-link">
                        <i class="fa-solid fa-arrow-left"></i> Sudah punya akun? Login
                    </a>
                </div>
            </form>
        </div>

        <div class="footer">&copy; {{ date('Y') }} NusaLearn Management System &mdash; Secured by Enterprise Security.</div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
            else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
        }
        window.addEventListener('load', function() {
            const pre = document.getElementById('preloader');
            pre.style.opacity = '0';
            setTimeout(() => { pre.style.visibility = 'hidden'; }, 600);
        });
    </script>
</body>
</html>