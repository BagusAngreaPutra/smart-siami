<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login SMART SIAMI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/smart-siami-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/smart-siami-icon.png') }}">
    <style>
        @font-face {
            font-family: "SIAMI Jakarta";
            src: url("{{ asset('fonts/plus-jakarta-sans-latin-wght-normal.woff2') }}") format("woff2");
            font-style: normal;
            font-weight: 200 800;
            font-display: swap;
        }

        :root {
            --page: #e8edf7;
            --surface: #ffffff;
            --text: #1d3152;
            --muted: #7d899d;
            --line: #dce4f0;
            --blue: #3478e5;
            --blue-strong: #2868d3;
            --blue-soft: #edf4ff;
            --violet: #7768da;
            --orange: #f08a4b;
            --teal: #2ba78e;
            --danger: #b54750;
            --shadow: 0 28px 75px rgba(44, 58, 89, .16);
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-width: 320px;
            background: var(--page);
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 42px;
            display: grid;
            place-items: center;
            overflow-x: hidden;
            color: var(--text);
            background:
                linear-gradient(110deg, transparent 0 7%, rgba(255, 255, 255, .36) 7.1% 7.25%, transparent 7.35% 18%, rgba(255, 255, 255, .30) 18.1% 18.25%, transparent 18.35% 100%),
                radial-gradient(circle at 4% 96%, rgba(93, 156, 220, .22) 0 13%, transparent 13.1%),
                radial-gradient(circle at 98% 8%, rgba(119, 104, 218, .10), transparent 28%),
                linear-gradient(135deg, #e7e7ee, #e5edfb 54%, #dce5f4);
            font-family: "SIAMI Jakarta", Arial, sans-serif;
            text-rendering: optimizeLegibility;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            z-index: 0;
            border-radius: 50%;
            pointer-events: none;
        }

        body::before {
            right: -72px;
            bottom: -80px;
            width: 220px;
            height: 220px;
            border: 1px solid rgba(240, 138, 75, .60);
        }

        body::after {
            right: 5.5%;
            bottom: 9%;
            width: 20px;
            height: 20px;
            background: var(--blue);
            box-shadow: -76vw -72vh 0 9px rgba(255, 255, 255, .28);
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(1180px, 100%);
            min-height: min(740px, calc(100vh - 84px));
            display: grid;
            grid-template-columns: minmax(0, 1.04fr) minmax(440px, .96fr);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .82);
            border-radius: 26px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .visual-panel {
            position: relative;
            min-width: 0;
            min-height: 650px;
            overflow: hidden;
            background: #eaf4ff;
        }

        .visual-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background:
                linear-gradient(180deg, rgba(232, 244, 255, .86) 0%, rgba(232, 244, 255, .16) 34%, transparent 57%),
                linear-gradient(90deg, rgba(232, 244, 255, .22), transparent 36%);
        }

        .visual-illustration {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .visual-copy {
            position: relative;
            z-index: 2;
            max-width: 475px;
            padding: 58px 50px;
        }

        .visual-eyebrow,
        .form-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--blue-strong);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .visual-eyebrow::before,
        .form-eyebrow::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--orange);
            box-shadow: 0 0 0 5px rgba(240, 138, 75, .13);
        }

        .visual-copy h1 {
            margin: 17px 0 13px;
            font-size: clamp(27px, 3vw, 38px);
            line-height: 1.2;
            letter-spacing: -.04em;
        }

        .visual-copy h1 .blue {
            color: var(--blue);
        }

        .visual-copy h1 .orange {
            color: var(--orange);
        }

        .visual-flow {
            position: absolute;
            z-index: 3;
            left: 36px;
            right: 36px;
            bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin: 0;
            padding: 13px 16px;
            border: 1px solid rgba(255, 255, 255, .72);
            border-radius: 15px;
            color: #48617f;
            background: rgba(255, 255, 255, .72);
            box-shadow: 0 14px 32px rgba(67, 94, 135, .12);
            backdrop-filter: blur(12px);
            font-size: 11px;
            font-weight: 750;
        }

        .visual-flow span {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--blue);
        }

        .visual-flow span:nth-of-type(2) {
            background: var(--violet);
        }

        .visual-flow span:nth-of-type(3) {
            background: var(--orange);
        }

        .form-panel {
            position: relative;
            min-width: 0;
            padding: 52px 70px 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(135deg, transparent 0 91%, rgba(52, 120, 229, .055) 91% 100%),
                #ffffff;
        }

        .form-panel::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 104px;
            height: 32px;
            opacity: .6;
            background: repeating-linear-gradient(48deg, transparent 0 9px, rgba(52, 120, 229, .46) 10px 11px);
        }

        .login-card {
            width: min(100%, 410px);
        }

        .brand-lockup {
            width: 188px;
            height: 58px;
            margin: 0 auto 29px;
            overflow: hidden;
        }

        .brand-lockup img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
        }

        .login-heading {
            margin-bottom: 26px;
            text-align: center;
        }

        .form-eyebrow {
            justify-content: center;
            color: var(--violet);
            font-size: 10px;
        }

        .form-eyebrow::before {
            background: var(--violet);
            box-shadow: 0 0 0 5px rgba(119, 104, 218, .11);
        }

        .login-heading h2 {
            margin: 13px 0 8px;
            color: #203a60;
            font-size: clamp(24px, 2.4vw, 31px);
            line-height: 1.25;
            letter-spacing: -.035em;
        }

        .login-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.65;
        }

        .login-form {
            display: grid;
            gap: 17px;
        }

        .field {
            display: grid;
            gap: 7px;
        }

        .field label {
            color: #475a76;
            font-size: 12px;
            font-weight: 750;
        }

        .input-control {
            position: relative;
        }

        .input-control input {
            width: 100%;
            min-height: 54px;
            padding: 14px 46px;
            color: var(--text);
            border: 1px solid var(--line);
            border-radius: 11px;
            outline: 0;
            background: #fbfdff;
            font: 600 13px/1.3 "SIAMI Jakarta", sans-serif;
            transition: border-color .17s ease, box-shadow .17s ease, background .17s ease;
        }

        .input-control input::placeholder {
            color: #a5afbf;
            font-weight: 500;
        }

        .input-control input:hover {
            border-color: #c6d4e8;
        }

        .input-control input:focus {
            border-color: rgba(52, 120, 229, .70);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(52, 120, 229, .11);
        }

        .input-icon {
            position: absolute;
            z-index: 1;
            top: 50%;
            left: 16px;
            width: 18px;
            height: 18px;
            fill: none;
            stroke: #7794c4;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            z-index: 2;
            top: 50%;
            right: 10px;
            width: 36px;
            height: 36px;
            padding: 0;
            display: grid;
            place-items: center;
            color: #7993bd;
            border: 0;
            border-radius: 9px;
            background: transparent;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            color: var(--blue);
            background: var(--blue-soft);
            outline: 0;
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .password-toggle .eye-off,
        .password-toggle.is-visible .eye-open {
            display: none;
        }

        .password-toggle.is-visible .eye-off {
            display: block;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 1px 0 2px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #6f7c90;
            font-size: 11px;
            cursor: pointer;
        }

        .remember input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
        }

        .remember-box {
            width: 17px;
            height: 17px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: #ffffff;
            border: 1px solid #cbd6e5;
            border-radius: 5px;
            background: #ffffff;
            transition: background .16s ease, border-color .16s ease, box-shadow .16s ease;
        }

        .remember-box svg {
            width: 11px;
            height: 11px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.4;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: 0;
        }

        .remember input:checked + .remember-box {
            border-color: var(--blue);
            background: var(--blue);
            box-shadow: 0 4px 11px rgba(52, 120, 229, .22);
        }

        .remember input:checked + .remember-box svg {
            opacity: 1;
        }

        .remember input:focus-visible + .remember-box {
            outline: 3px solid rgba(52, 120, 229, .16);
        }

        .submit-button {
            width: 100%;
            min-height: 54px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #ffffff;
            border: 1px solid var(--blue);
            border-radius: 11px;
            background: linear-gradient(135deg, #3478e5, #438bea);
            box-shadow: 0 12px 24px rgba(52, 120, 229, .23);
            font: 800 13px/1 "SIAMI Jakarta", sans-serif;
            cursor: pointer;
            transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .submit-button svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: transform .16s ease;
        }

        .submit-button:hover,
        .submit-button:focus-visible {
            background: linear-gradient(135deg, #2868d3, #3478e5);
            box-shadow: 0 16px 30px rgba(52, 120, 229, .29);
            transform: translateY(-1px);
            outline: 0;
        }

        .submit-button:hover svg {
            transform: translateX(3px);
        }

        .error {
            padding: 9px 11px;
            color: var(--danger);
            border: 1px solid #f1d3d6;
            border-radius: 9px;
            background: #fff4f5;
            font-size: 11px;
            line-height: 1.45;
        }

        .workspace-access {
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #edf0f5;
            text-align: center;
        }

        .workspace-access > span {
            display: block;
            margin-bottom: 12px;
            color: #9aa4b4;
            font-size: 10px;
            font-weight: 650;
        }

        .role-chips {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
        }

        .role-chip {
            min-width: 0;
            min-height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #5d6c84;
            border: 1px solid #e2e8f1;
            border-radius: 10px;
            background: #ffffff;
            font-size: 9px;
            font-weight: 750;
        }

        .role-chip i {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--violet);
            box-shadow: 0 0 0 4px rgba(119, 104, 218, .09);
        }

        .role-chip:nth-child(2) i {
            background: var(--teal);
            box-shadow: 0 0 0 4px rgba(43, 167, 142, .09);
        }

        .role-chip:nth-child(3) i {
            background: var(--orange);
            box-shadow: 0 0 0 4px rgba(240, 138, 75, .09);
        }

        .security-line {
            margin: 24px 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            color: #9ba5b4;
            font-size: 9px;
            text-align: center;
        }

        .security-line svg {
            width: 14px;
            height: 14px;
            flex: 0 0 auto;
            fill: none;
            stroke: #6f94d2;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        @media (max-width: 1020px) {
            body {
                padding: 24px;
            }

            .login-shell {
                min-height: calc(100vh - 48px);
                grid-template-columns: minmax(0, .95fr) minmax(410px, 1.05fr);
            }

            .visual-copy {
                padding: 46px 34px;
            }

            .form-panel {
                padding-inline: 44px;
            }
        }

        @media (max-width: 820px) {
            body {
                padding: 14px;
                place-items: start center;
            }

            .login-shell {
                min-height: auto;
                grid-template-columns: minmax(0, 1fr);
                border-radius: 20px;
            }

            .visual-panel {
                min-height: 390px;
            }

            .visual-illustration {
                object-position: center 57%;
            }

            .visual-copy {
                max-width: 520px;
                padding: 36px;
            }

            .visual-copy h1 {
                max-width: 430px;
                font-size: 29px;
            }

            .visual-flow {
                left: 20px;
                right: 20px;
                bottom: 18px;
            }

            .form-panel {
                padding: 46px 38px 36px;
            }
        }

        @media (max-width: 520px) {
            body {
                padding: 0;
                background: #ffffff;
            }

            body::before,
            body::after {
                display: none;
            }

            .login-shell {
                width: 100%;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .visual-panel {
                min-height: 340px;
            }

            .visual-copy {
                padding: 27px 23px;
            }

            .visual-eyebrow {
                font-size: 9px;
            }

            .visual-copy h1 {
                max-width: 340px;
                margin-top: 12px;
                font-size: 23px;
            }

            .visual-flow {
                gap: 6px;
                padding: 11px;
                font-size: 8px;
            }

            .form-panel {
                padding: 37px 22px 30px;
            }

            .brand-lockup {
                width: 168px;
                height: 52px;
                margin-bottom: 24px;
            }

            .login-heading h2 {
                font-size: 25px;
            }

            .role-chips {
                gap: 6px;
            }

            .role-chip {
                font-size: 8px;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="visual-panel" aria-label="Ilustrasi kolaborasi audit mutu">
            <img
                class="visual-illustration"
                src="{{ asset('images/auth/smart-siami-login-illustration.webp') }}"
                alt="Auditor meninjau checklist bersama perwakilan unit melalui ruang kerja digital"
            >

            <div class="visual-copy">
                <span class="visual-eyebrow">Sistem Informasi Audit Mutu Internal</span>
                <h1><span class="blue">Audit mutu</span> lebih terarah dan <span class="orange">transparan.</span></h1>
            </div>

            <p class="visual-flow">
                Audit <span></span> Bukti Dokumen <span></span> Temuan <span></span> Tindak Lanjut
            </p>
        </section>

        <section class="form-panel">
            <div class="login-card">
                <div class="brand-lockup">
                    <img src="{{ asset('images/brand/smart-siami-lockup.png') }}" alt="SMART SIAMI">
                </div>

                <header class="login-heading">
                    <span class="form-eyebrow">Portal akses institusi</span>
                    <h2>Hai, selamat datang kembali</h2>
                    <p>Masuk menggunakan akun institusi untuk melanjutkan pekerjaan Anda.</p>
                </header>

                <form class="login-form" method="post" action="{{ route('login.store') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email institusi</label>
                        <div class="input-control">
                            <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Contoh: nama@institusi.ac.id" autocomplete="email" autofocus required>
                        </div>
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Kata sandi</label>
                        <div class="input-control password-control">
                            <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                            <input id="password" name="password" type="password" placeholder="Masukkan kata sandi Anda" autocomplete="current-password" required>
                            <button class="password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false">
                                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"></path><path d="M10.6 10.6A3 3 0 0 0 13.4 13.4"></path><path d="M9.9 4.4A10.5 10.5 0 0 1 12 4.2c6.5 0 10 7 10 7a18.7 18.7 0 0 1-3 4"></path><path d="M6.6 6.6C3.6 8.5 2 12 2 12s3.5 7 10 7a10.8 10.8 0 0 0 5.4-1.4"></path></svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-options">
                        <label class="remember">
                            <input type="checkbox" name="remember" value="1">
                            <span class="remember-box" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="m3 8 3 3 7-7"></path></svg></span>
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button class="submit-button" type="submit">
                        <span>Masuk ke SMART SIAMI</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                    </button>
                </form>

                <div class="workspace-access">
                    <span>Satu akun untuk workspace sesuai peran</span>
                    <div class="role-chips" aria-label="Peran pengguna">
                        <div class="role-chip"><i></i> Administrator</div>
                        <div class="role-chip"><i></i> Auditor</div>
                        <div class="role-chip"><i></i> Auditee</div>
                    </div>
                </div>

                <p class="security-line">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
                    Akses dilindungi autentikasi dan koneksi yang aman
                </p>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = button.closest('.password-control')?.querySelector('input');
                if (! input) return;

                const isVisible = input.type === 'text';
                input.type = isVisible ? 'password' : 'text';
                button.classList.toggle('is-visible', ! isVisible);
                button.setAttribute('aria-pressed', String(! isVisible));
                button.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
                input.focus({ preventScroll: true });
            });
        });
    </script>
</body>
</html>
