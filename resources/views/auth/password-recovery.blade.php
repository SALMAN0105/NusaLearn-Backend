<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pemulihan Akses - NusaLearn</title>
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
            position: relative; padding: 24px 0;
        }

        /* PRELOADER */
        #preloader { position: fixed; inset: 0; background: var(--white); z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity .6s ease, visibility .6s ease; }
        .pre-logo { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 2.5rem; color: var(--black); letter-spacing: -2px; display: flex; align-items: center; gap: 6px; animation: pre-pulse 1.2s ease-in-out infinite; }
        .pre-logo span { color: var(--purple); }
        @keyframes pre-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* BG SCENE */
        .bg-scene { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .blob-tl { position: absolute; top: -120px; left: -100px; width: 520px; height: 520px; background: var(--purple); border-radius: 60% 40% 70% 30% / 50% 60% 40% 50%; opacity: .10; animation: morph-a 12s ease-in-out infinite; }
        .blob-br { position: absolute; bottom: -100px; right: -80px; width: 440px; height: 440px; background: var(--purple-mid); border-radius: 40% 60% 30% 70% / 60% 40% 50% 50%; opacity: .08; animation: morph-b 14s ease-in-out infinite; }
        .dot-grid { position: absolute; inset: 0; background-image: radial-gradient(circle, #7C3AED22 1px, transparent 1px); background-size: 28px 28px; }
        .deco-ring { position: absolute; bottom: 80px; left: 60px; width: 100px; height: 100px; border: 12px solid var(--purple); border-radius: 50%; opacity: .15; animation: spin-slow 20s linear infinite; }
        .deco-chunky { position: absolute; top: 8%; right: 7%; width: 70px; height: 70px; background: var(--purple-lt); border: 3px solid var(--black); border-radius: 18px; box-shadow: 5px 5px 0 var(--black); transform: rotate(18deg); animation: float-y 6s ease-in-out infinite 1s; }
        .deco-star { position: absolute; bottom: 25%; left: 10%; font-size: 44px; line-height: 1; animation: spin-slow 15s linear infinite reverse; opacity: .25; color: var(--purple); }

        /* Step badge melayang */
        .float-step { position: absolute; top: 18%; left: 6%; background: var(--white); border: 2.5px solid var(--black); border-radius: 16px; box-shadow: 4px 4px 0 var(--black); padding: 10px 18px; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 12px; color: var(--black); display: flex; align-items: center; gap: 8px; animation: float-y 4s ease-in-out infinite; white-space: nowrap; }
        .float-step i { color: var(--purple); font-size: 14px; }

        @keyframes morph-a { 0%,100%{border-radius:60% 40% 70% 30%/50% 60% 40% 50%} 50%{border-radius:30% 70% 40% 60%/60% 30% 70% 40%} }
        @keyframes morph-b { 0%,100%{border-radius:40% 60% 30% 70%/60% 40% 50% 50%} 50%{border-radius:70% 30% 60% 40%/30% 70% 40% 60%} }
        @keyframes float-y { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }
        @keyframes spin-slow { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
        @keyframes slide-up { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:translateY(0)} }

        /* WRAP */
        .recovery-wrap { position: relative; z-index: 10; width: 100%; max-width: 460px; padding: 0 20px; }

        /* BRAND */
        .brand { text-align: center; margin-bottom: 28px; animation: slide-up .5s ease both; }
        .brand-icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: var(--purple); border: 3px solid var(--black); border-radius: 20px; box-shadow: 5px 5px 0 var(--black); margin-bottom: 14px; font-size: 26px; color: var(--white); transform: rotate(-6deg); transition: transform .3s; }
        .brand-icon:hover { transform: rotate(0deg); }
        .brand h1 { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 2.2rem; letter-spacing: -2px; color: var(--black); margin: 0 0 6px; line-height: 1; }
        .brand h1 span { color: var(--purple); }
        .brand p { font-size: 13px; font-weight: 600; color: #6B7280; margin: 0; }

        /* CARD */
        .card { background: var(--white); border: 2.5px solid var(--black); border-radius: 28px; box-shadow: 8px 8px 0 var(--black); padding: 36px 36px 32px; animation: slide-up .55s ease .08s both; }
        @media (max-width: 480px) { .card { padding: 24px 20px; } }

        /* STEP INDICATOR */
        .step-bar { display: flex; align-items: center; gap: 0; margin-bottom: 28px; }
        .step-item { display: flex; flex-direction: column; align-items: center; flex: 1; }
        .step-dot { width: 32px; height: 32px; border-radius: 50%; border: 2.5px solid var(--black); display: flex; align-items: center; justify-content: center; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 13px; background: var(--white); color: #9CA3AF; box-shadow: 2px 2px 0 var(--black); transition: all .3s; }
        .step-dot.active { background: var(--purple); color: var(--white); border-color: var(--purple); box-shadow: 3px 3px 0 var(--black); }
        .step-dot.done { background: #22C55E; color: var(--white); border-color: #16A34A; }
        .step-label { font-size: 10px; font-weight: 700; color: #9CA3AF; margin-top: 4px; text-align: center; }
        .step-label.active { color: var(--purple); }
        .step-line { flex: 1; height: 2.5px; background: #E5E7EB; margin: 0 4px; margin-bottom: 18px; border-radius: 99px; transition: background .3s; }
        .step-line.done { background: var(--purple); }

        /* SECTION HEADING */
        .section-head { margin-bottom: 20px; }
        .section-head h2 { font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 1.4rem; letter-spacing: -0.5px; color: var(--black); margin: 0 0 4px; }
        .section-head p { font-size: 13px; font-weight: 500; color: #9CA3AF; margin: 0; }

        /* ALERT */
        .alert { padding: 12px 16px; border-radius: 14px; font-size: 13px; font-weight: 600; display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; border: 2px solid; }
        .alert-success { background: #F0FDF4; border-color: #16A34A; color: #15803D; }
        .alert-error   { background: #FFF1F2; border-color: #F43F5E; color: #BE123C; }

        /* LABEL & INPUT */
        .field-label { display: block; font-size: 13px; font-weight: 700; color: var(--black); margin-bottom: 8px; }
        .input-wrap { position: relative; margin-bottom: 18px; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #A78BFA; font-size: 15px; transition: color .2s; pointer-events: none; }
        .input-wrap:focus-within .input-icon { color: var(--purple); }
        .field-input { width: 100%; background: var(--gray-soft); border: 2px solid #E5E7EB; border-radius: 14px; padding: 14px 16px 14px 44px; font-size: 14px; font-weight: 500; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--black); transition: border-color .2s, box-shadow .2s, background .2s; outline: none; }
        .field-input::placeholder { color: #C4B5FD; }
        .field-input:focus { border-color: var(--purple); background: var(--white); box-shadow: 0 0 0 4px #7C3AED1A, 3px 3px 0 var(--purple); }
        .toggle-pw { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #A78BFA; font-size: 14px; transition: color .2s; padding: 4px; }
        .toggle-pw:hover { color: var(--purple); }

        /* OTP INPUT */
        .otp-input { width: 100%; background: var(--gray-soft); border: 2.5px solid var(--black); border-radius: 14px; padding: 18px 16px; font-size: 2rem; font-weight: 900; font-family: 'Outfit', monospace; letter-spacing: .6em; text-align: center; color: var(--purple); outline: none; box-shadow: 3px 3px 0 var(--black); transition: box-shadow .2s, border-color .2s; }
        .otp-input:focus { border-color: var(--purple); box-shadow: 5px 5px 0 var(--black); }
        .otp-input::placeholder { color: #DDD6FE; letter-spacing: .5em; }

        /* OTP ICON BOX */
        .otp-icon { width: 72px; height: 72px; background: var(--purple-lt); border: 2.5px solid var(--black); border-radius: 20px; box-shadow: 4px 4px 0 var(--black); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px; color: var(--purple); }

        /* WARNING BOX */
        .warn-box { background: #FFFBEB; border: 2px solid #F59E0B; border-radius: 12px; padding: 10px 14px; font-size: 12px; font-weight: 600; color: #92400E; margin-bottom: 20px; }

        /* BTN */
        .btn-submit { width: 100%; background: var(--purple); color: var(--white); border: 2.5px solid var(--black); border-radius: 14px; padding: 15px; font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800; letter-spacing: .3px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 4px 4px 0 var(--black); transition: transform .15s, box-shadow .15s, background .15s; outline: none; }
        .btn-submit:hover { background: var(--purple-dark); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--black); }
        .btn-submit:active { transform: translate(2px, 2px); box-shadow: 2px 2px 0 var(--black); }

        /* BACK LINK */
        .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #6B7280; text-decoration: none; padding: 8px 16px; border: 2px solid #E5E7EB; border-radius: 99px; transition: all .2s; margin-top: 20px; }
        .back-link:hover { border-color: var(--purple); color: var(--purple); background: var(--purple-lt); }

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
        <div class="deco-chunky"></div>
        <div class="deco-star">✦</div>
        <div class="float-step">
            <i class="fa-solid fa-shield-halved"></i> Pemulihan Aman
        </div>
    </div>

    <div class="recovery-wrap">
        <div class="brand">
            <div class="brand-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <h1>Nusa<span>Learn</span></h1>
            <p>Pemulihan Akses Guru</p>
        </div>

        <div class="card">

            {{-- Step Indicator --}}
            <div class="step-bar">
                <div class="step-item">
                    <div class="step-dot {{ $step === 'request_email' ? 'active' : 'done' }}">
                        {{ $step === 'request_email' ? '1' : '✓' }}
                    </div>
                    <div class="step-label {{ $step === 'request_email' ? 'active' : '' }}">Email</div>
                </div>
                <div class="step-line {{ in_array($step, ['verify_otp','reset_password']) ? 'done' : '' }}"></div>
                <div class="step-item">
                    <div class="step-dot {{ $step === 'verify_otp' ? 'active' : ($step === 'reset_password' ? 'done' : '') }}">
                        {{ $step === 'reset_password' ? '✓' : '2' }}
                    </div>
                    <div class="step-label {{ $step === 'verify_otp' ? 'active' : '' }}">Kode OTP</div>
                </div>
                <div class="step-line {{ $step === 'reset_password' ? 'done' : '' }}"></div>
                <div class="step-item">
                    <div class="step-dot {{ $step === 'reset_password' ? 'active' : '' }}">3</div>
                    <div class="step-label {{ $step === 'reset_password' ? 'active' : '' }}">Password Baru</div>
                </div>
            </div>

            @if (session('success'))
            <div class="alert alert-success"><i class="fa-solid fa-circle-check" style="margin-top:1px"></i><span>{{ session('success') }}</span></div>
            @endif
            @if ($errors->any())
            <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation" style="margin-top:1px"></i><span>{{ $errors->first() }}</span></div>
            @endif

            {{-- STEP 1: Request Email --}}
            @if($step === 'request_email')
            <div class="section-head">
                <h2>Masukkan Email Anda</h2>
                <p>Kami akan mengirimkan kode verifikasi 6 digit ke email terdaftar.</p>
            </div>
            <form method="POST" action="{{ route('password.sendOtp') }}">
                @csrf
                <label class="field-label">Alamat Email Valid</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input class="field-input" type="email" name="email" required autofocus placeholder="guru@sekolah.sch.id">
                </div>
                <button class="btn-submit" type="submit">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Kode OTP
                </button>
            </form>

            {{-- STEP 2: Verify OTP --}}
            @elseif($step === 'verify_otp')
            <div class="otp-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
            <div class="section-head" style="text-align:center">
                <h2>Cek Email Anda</h2>
                <p>Kode OTP 6 digit dikirim ke <strong style="color:var(--purple)">{{ session('reset_email') }}</strong></p>
            </div>
            <form method="POST" action="{{ route('password.verifyOtp') }}">
                @csrf
                <div style="margin-bottom:20px">
                    <input class="otp-input" type="text" name="otp" required maxlength="6" autofocus placeholder="——————" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>
                <button class="btn-submit" type="submit">
                    <i class="fa-solid fa-check-double"></i> Verifikasi Kode
                </button>
            </form>

            {{-- STEP 3: Reset Password --}}
            @elseif($step === 'reset_password')
            <div class="section-head">
                <h2>Buat Password Baru</h2>
                <p>Buat kata_sandi yang kuat untuk akun Anda.</p>
            </div>
            <div class="warn-box">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Minimal 8 karakter, mengandung huruf besar, kecil, angka & simbol.
            </div>
            <form method="POST" action="{{ route('password.resetPassword') }}">
                @csrf
                <label class="field-label">Password Baru</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-key input-icon"></i>
                    <input class="field-input" id="kata_sandi" type="password" name="kata_sandi" required placeholder="••••••••" style="padding-right:46px">
                    <button type="button" class="toggle-pw" onclick="togglePassword('kata_sandi','eye-p')"><i id="eye-p" class="fa-regular fa-eye"></i></button>
                </div>
                <label class="field-label">Konfirmasi Password Baru</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input class="field-input" id="kata_sandi_confirmation" type="password" name="kata_sandi_confirmation" required placeholder="••••••••" style="padding-right:46px">
                    <button type="button" class="toggle-pw" onclick="togglePassword('kata_sandi_confirmation','eye-c')"><i id="eye-c" class="fa-regular fa-eye"></i></button>
                </div>
                <button class="btn-submit" type="submit">
                    <i class="fa-solid fa-rotate-right"></i> Update Password & Login
                </button>
            </form>
            @endif

            <div style="text-align:center">
                <a href="{{ route('login') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Login
                </a>
            </div>
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
