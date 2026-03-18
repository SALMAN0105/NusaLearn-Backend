<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - NusaLearn</title>
    
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
            margin: 0;
            min-height: 100vh;
            background: var(--white);
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* ── PRELOADER ── */
        #preloader {
            position: fixed; inset: 0;
            background: var(--white);
            z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            transition: opacity .6s ease, visibility .6s ease;
        }
        .pre-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 2.5rem;
            color: var(--black);
            letter-spacing: -2px;
            display: flex; align-items: center; gap: 6px;
            animation: pre-pulse 1.2s ease-in-out infinite;
        }
        .pre-logo span { color: var(--purple); }
        @keyframes pre-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* ── DECORATIVE BG BLOBS ── */
        .bg-scene {
            position: fixed; inset: 0;
            pointer-events: none; z-index: 0;
            overflow: hidden;
        }

        /* Blob ungu besar kiri atas */
        .blob-tl {
            position: absolute;
            top: -120px; left: -100px;
            width: 520px; height: 520px;
            background: var(--purple);
            border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%;
            opacity: .12;
            animation: morph-a 12s ease-in-out infinite;
        }
        /* Blob ungu kanan bawah */
        .blob-br {
            position: absolute;
            bottom: -100px; right: -80px;
            width: 440px; height: 440px;
            background: var(--purple-mid);
            border-radius: 40% 60% 30% 70% / 60% 40% 50% 50%;
            opacity: .1;
            animation: morph-b 14s ease-in-out infinite;
        }
        /* Cincin dekoratif kiri bawah */
        .deco-ring {
            position: absolute;
            bottom: 80px; left: 60px;
            width: 120px; height: 120px;
            border: 14px solid var(--purple);
            border-radius: 50%;
            opacity: .18;
            animation: spin-slow 20s linear infinite;
        }
        /* Squiggle / lengkungan kanan atas */
        .deco-arc {
            position: absolute;
            top: 60px; right: 120px;
            width: 90px; height: 90px;
            border: 12px solid var(--purple);
            border-radius: 50%;
            clip-path: inset(0 0 50% 0);
            opacity: .22;
            transform: rotate(-30deg);
        }
        /* Titik-titik grid halus */
        .dot-grid {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, #7C3AED22 1px, transparent 1px);
            background-size: 28px 28px;
        }
        /* Badge melayang kiri */
        .float-badge {
            position: absolute;
            top: 22%; left: 6%;
            background: var(--white);
            border: 2.5px solid var(--black);
            border-radius: 16px;
            box-shadow: 4px 4px 0 var(--black);
            padding: 12px 20px;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 13px;
            color: var(--black);
            display: flex; align-items: center; gap: 10px;
            animation: float-y 4s ease-in-out infinite;
            white-space: nowrap;
        }
        .float-badge .dot { width: 10px; height: 10px; background: #22C55E; border-radius: 50%; border: 2px solid var(--black); }
        /* Badge kanan bawah */
        .float-badge-2 {
            position: absolute;
            bottom: 20%; right: 6%;
            background: var(--purple);
            border: 2.5px solid var(--black);
            border-radius: 16px;
            box-shadow: 4px 4px 0 var(--black);
            padding: 12px 20px;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 13px;
            color: var(--white);
            display: flex; align-items: center; gap: 8px;
            animation: float-y 5s ease-in-out infinite .8s;
            white-space: nowrap;
        }
        /* Chunky shape kanan atas */
        .deco-chunky {
            position: absolute;
            top: 10%; right: 8%;
            width: 80px; height: 80px;
            background: var(--purple-lt);
            border: 3px solid var(--black);
            border-radius: 20px;
            box-shadow: 6px 6px 0 var(--black);
            transform: rotate(18deg);
            animation: float-y 6s ease-in-out infinite 1.2s;
        }
        /* Bintang kecil */
        .deco-star {
            position: absolute;
            bottom: 28%; left: 12%;
            font-size: 48px;
            line-height: 1;
            animation: spin-slow 15s linear infinite reverse;
            opacity: .35;
        }

        @keyframes morph-a { 0%,100%{border-radius:60% 40% 70% 30%/50% 60% 40% 50%} 50%{border-radius:30% 70% 40% 60%/60% 30% 70% 40%} }
        @keyframes morph-b { 0%,100%{border-radius:40% 60% 30% 70%/60% 40% 50% 50%} 50%{border-radius:70% 30% 60% 40%/30% 70% 40% 60%} }
        @keyframes float-y { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }
        @keyframes spin-slow { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

        /* ── MAIN CARD ── */
        .login-wrap {
            position: relative; z-index: 10;
            width: 100%; max-width: 460px;
            padding: 0 20px;
        }

        /* Brand header */
        .brand {
            text-align: center;
            margin-bottom: 32px;
            animation: slide-up .5s ease both;
        }
        .brand-icon {
            display: inline-flex;
            align-items: center; justify-content: center;
            width: 68px; height: 68px;
            background: var(--purple);
            border: 3px solid var(--black);
            border-radius: 20px;
            box-shadow: 5px 5px 0 var(--black);
            margin-bottom: 18px;
            font-size: 28px;
            color: var(--white);
            transform: rotate(-6deg);
            transition: transform .3s;
        }
        .brand-icon:hover { transform: rotate(0deg); }
        .brand h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 2.4rem;
            letter-spacing: -2px;
            color: var(--black);
            margin: 0 0 6px;
            line-height: 1;
        }
        .brand h1 span { color: var(--purple); }
        .brand p {
            font-size: 13px;
            font-weight: 600;
            color: #6B7280;
            margin: 0;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .brand p .chip {
            background: var(--purple-lt);
            color: var(--purple-dark);
            font-size: 11px;
            font-weight: 800;
            padding: 2px 10px;
            border-radius: 99px;
            border: 1.5px solid var(--purple);
            letter-spacing: .5px;
        }

        /* Card form */
        .card {
            background: var(--white);
            border: 2.5px solid var(--black);
            border-radius: 28px;
            box-shadow: 8px 8px 0 var(--black);
            padding: 40px 40px 36px;
            animation: slide-up .55s ease .08s both;
        }
        @media (max-width: 480px) { .card { padding: 28px 22px 24px; } }

        .card-heading {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 1.55rem;
            letter-spacing: -1px;
            color: var(--black);
            margin: 0 0 4px;
        }
        .card-sub {
            font-size: 13px; font-weight: 500;
            color: #9CA3AF;
            margin: 0 0 28px;
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: flex-start; gap: 10px;
            margin-bottom: 20px;
            border: 2px solid;
        }
        .alert-success { background: #F0FDF4; border-color: #16A34A; color: #15803D; }
        .alert-error   { background: #FFF1F2; border-color: #F43F5E; color: #BE123C; }

        /* Label */
        .field-label {
            display: block;
            font-size: 13px; font-weight: 700;
            color: var(--black);
            margin-bottom: 8px;
            letter-spacing: .2px;
        }

        /* Input */
        .input-wrap { position: relative; margin-bottom: 18px; }
        .input-icon {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%);
            color: #A78BFA;
            font-size: 15px;
            transition: color .2s;
            pointer-events: none;
        }
        .input-wrap:focus-within .input-icon { color: var(--purple); }

        .field-input {
            width: 100%;
            background: var(--gray-soft);
            border: 2px solid #E5E7EB;
            border-radius: 14px;
            padding: 14px 16px 14px 44px;
            font-size: 14px; font-weight: 500;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--black);
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }
        .field-input::placeholder { color: #C4B5FD; }
        .field-input:focus {
            border-color: var(--purple);
            background: var(--white);
            box-shadow: 0 0 0 4px #7C3AED1A, 3px 3px 0 var(--purple);
        }

        /* Toggle password */
        .toggle-pw {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #A78BFA; font-size: 14px;
            transition: color .2s;
            padding: 4px;
        }
        .toggle-pw:hover { color: var(--purple); }

        /* Remember + Forgot */
        .form-meta {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 26px;
        }
        .remember-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; font-weight: 600; color: #374151;
            cursor: pointer;
        }
        .remember-box {
            appearance: none; width: 18px; height: 18px;
            border: 2px solid var(--purple);
            border-radius: 6px; background: var(--white);
            cursor: pointer; position: relative;
            transition: background .15s;
        }
        .remember-box:checked { background: var(--purple); }
        .remember-box:checked::after {
            content: '';
            position: absolute; top: 2px; left: 5px;
            width: 5px; height: 9px;
            border: 2px solid var(--white);
            border-top: none; border-left: none;
            transform: rotate(45deg);
        }
        .forgot-link {
            font-size: 13px; font-weight: 700;
            color: var(--purple);
            text-decoration: none;
            transition: color .2s;
        }
        .forgot-link:hover { color: var(--purple-dark); text-decoration: underline; }

        /* Submit btn */
        .btn-submit {
            width: 100%;
            background: var(--purple);
            color: var(--white);
            border: 2.5px solid var(--black);
            border-radius: 14px;
            padding: 15px;
            font-family: 'Outfit', sans-serif;
            font-size: 16px; font-weight: 800;
            letter-spacing: .3px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 4px 4px 0 var(--black);
            transition: transform .15s, box-shadow .15s, background .15s;
            outline: none;
        }
        .btn-submit:hover {
            background: var(--purple-dark);
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--black);
        }
        .btn-submit:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0 var(--black);
        }

        /* Register link */
        .register-row {
            text-align: center;
            margin-top: 20px;
        }
        .register-link {
            font-size: 13px; font-weight: 700;
            color: var(--purple);
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px;
            border: 2px solid var(--purple-lt);
            border-radius: 99px;
            background: var(--purple-lt);
            transition: all .2s;
        }
        .register-link:hover {
            background: var(--purple);
            color: var(--white);
            border-color: var(--purple);
            transform: translateY(-1px);
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 2px dashed #E5E7EB;
            margin: 24px 0 20px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 28px;
            font-size: 12px; font-weight: 500;
            color: #9CA3AF;
            animation: slide-up .6s ease .18s both;
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>

    <!-- ── PRELOADER ── -->
    <div id="preloader">
        <div class="pre-logo">
            Nusa<span>Learn</span>
            <span style="font-size:1.6rem; margin-left:4px;">✦</span>
        </div>
    </div>

    <!-- ── BACKGROUND SCENE ── -->
    <div class="bg-scene">
        <div class="dot-grid"></div>
        <div class="blob-tl"></div>
        <div class="blob-br"></div>
        <div class="deco-ring"></div>
        <div class="deco-arc"></div>
        <div class="deco-chunky"></div>

        <!-- Badge melayang kiri: status server -->
        <div class="float-badge">
            <div class="dot"></div>
            Server Online
        </div>

        <!-- Badge melayang kanan: versi -->
        <div class="float-badge-2">
            <i class="fa-solid fa-microchip" style="font-size:14px"></i>
            AI Powered v2.0
        </div>

        <!-- Bintang dekoratif -->
        <div class="deco-star" style="color: var(--purple);">✦</div>
    </div>

    <!-- ── LOGIN FORM ── -->
    <div class="login-wrap">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h1>Nusa<span>Learn</span></h1>
            <p>
                Administrator Gateway
                <span class="chip">ADMIN</span>
            </p>
        </div>

        <!-- Card -->
        <div class="card">
            <p class="card-heading">Selamat datang! 👋</p>
            <p class="card-sub">Masuk untuk mengakses panel administrator.</p>

            {{-- Alert Sukses --}}
            @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check" style="margin-top:1px"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            {{-- Alert Error --}}
            @if ($errors->any())
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation" style="margin-top:1px"></i>
                <div>
                    <strong style="display:block; margin-bottom:2px;">Otorisasi Ditolak!</strong>
                    <span style="font-weight:500">{{ $errors->first() }}</span>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <!-- Username -->
                <label class="field-label" for="username">Username Akses</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-user-shield input-icon"></i>
                    <input class="field-input" id="username" type="text"
                           name="username" value="{{ old('username') }}"
                           required autofocus placeholder="Masukkan username...">
                </div>

                <!-- Password -->
                <label class="field-label" for="password">Kunci Keamanan</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-key input-icon"></i>
                    <input class="field-input" id="password" type="password"
                           name="password" required
                           placeholder="••••••••" style="padding-right: 46px;">
                    <button type="button" class="toggle-pw"
                            onclick="togglePassword('password', 'eye-icon')">
                        <i id="eye-icon" class="fa-regular fa-eye"></i>
                    </button>
                </div>

                <!-- Meta row -->
                <div class="form-meta">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" class="remember-box">
                        Ingat sesi saya
                    </label>
                    <a href="{{ route('password.recovery') }}" class="forgot-link">
                        Lupa kata sandi?
                    </a>
                </div>

                <!-- Submit -->
                <button class="btn-submit" type="submit">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Masuk Sistem
                </button>

                <hr class="divider">

                <!-- Register -->
                <div class="register-row">
                    <a href="{{ route('admin.register') }}" class="register-link">
                        <i class="fa-solid fa-user-plus"></i>
                        Daftarkan Administrator Baru
                    </a>
                </div>

            </form>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} NusaLearn Management System &mdash; Secured by Enterprise Security.
        </div>

    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Preloader
        window.addEventListener('load', function () {
            const pre = document.getElementById('preloader');
            pre.style.opacity = '0';
            setTimeout(() => { pre.style.visibility = 'hidden'; }, 600);
        });
    </script>
</body>
</html>