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

        @font-face {
            font-family: "SIAMI Manrope";
            src: url("{{ asset('fonts/manrope-latin-wght-normal.woff2') }}") format("woff2");
            font-style: normal;
            font-weight: 200 800;
            font-display: swap;
        }

        :root {
            --page: #eef1f5;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --line: #e2e6ec;
            --line-strong: #cfd6e1;
            --text: #252d3d;
            --muted: #7c8594;
            --blue: #3979ed;
            --blue-soft: #edf3ff;
            --teal: #2eaa91;
            --teal-soft: #eaf8f5;
            --orange: #ef9d38;
            --orange-soft: #fff5e7;
            --violet: #7e68c9;
            --violet-soft: #f1edfb;
            --red: #c75d66;
            --shadow: 0 22px 60px rgba(31, 42, 61, .12);
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
            padding: 24px;
            display: grid;
            place-items: center;
            overflow-x: hidden;
            color: var(--text);
            background:
                radial-gradient(circle at 4% 12%, rgba(57, 121, 237, .10), transparent 28vw),
                radial-gradient(circle at 96% 86%, rgba(46, 170, 145, .10), transparent 30vw),
                var(--page);
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
            top: 5%;
            left: 2%;
            width: 110px;
            height: 110px;
            border: 24px solid rgba(57, 121, 237, .055);
        }

        body::after {
            right: 3%;
            bottom: 4%;
            width: 84px;
            height: 84px;
            background: rgba(46, 170, 145, .06);
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(1380px, 100%);
            min-height: min(760px, calc(100vh - 48px));
            display: grid;
            grid-template-columns: minmax(270px, .88fr) minmax(390px, 1.12fr) minmax(270px, .88fr);
            gap: 22px;
            background: transparent;
        }

        .side-panel {
            position: relative;
            min-width: 0;
            padding: 38px 34px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(207, 214, 225, .90);
            border-radius: 20px;
            box-shadow: 0 16px 42px rgba(31, 42, 61, .09);
        }

        .side-panel::before,
        .login-center::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            z-index: 2;
            height: 4px;
            pointer-events: none;
        }

        .side-panel::after {
            content: "";
            position: absolute;
            z-index: 0;
            width: 180px;
            height: 180px;
            border: 32px solid rgba(57, 121, 237, .035);
            border-radius: 50%;
            pointer-events: none;
        }

        .side-panel > * {
            position: relative;
            z-index: 1;
        }

        .intro-panel {
            background:
                radial-gradient(circle at 16% 10%, rgba(57, 121, 237, .10), transparent 30%),
                linear-gradient(155deg, #f7f9fd 0%, #f9fbff 54%, #f1f6ff 100%);
        }

        .intro-panel::before {
            background: linear-gradient(90deg, var(--blue), #75a0f2, var(--teal));
        }

        .intro-panel::after {
            right: -92px;
            bottom: 54px;
        }

        .role-panel {
            background:
                radial-gradient(circle at 92% 90%, rgba(46, 170, 145, .10), transparent 34%),
                linear-gradient(155deg, #fbfcfd 0%, #f7fbfa 100%);
        }

        .role-panel::before {
            background: linear-gradient(90deg, var(--teal), #69c7b5, var(--orange));
        }

        .role-panel::after {
            top: 82px;
            left: -105px;
            border-color: rgba(46, 170, 145, .04);
        }

        .brand-lockup-crop {
            position: relative;
            width: 244px;
            max-width: 100%;
            height: 64px;
            margin: -2px 0 30px;
            overflow: hidden;
        }

        .brand-lockup-image {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 248px;
            max-width: none;
            height: auto;
            display: block;
            border: 0;
            mix-blend-mode: multiply;
            transform: translate(-50%, -50%);
        }

        .mobile-brand {
            display: none;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
            color: var(--blue);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: "";
            width: 18px;
            height: 2px;
            background: currentColor;
            border-radius: 99px;
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        h1,
        h2,
        h3 {
            font-family: "SIAMI Manrope", "SIAMI Jakarta", sans-serif;
            color: var(--text);
        }

        h1 {
            max-width: 360px;
            margin-bottom: 13px;
            font-size: clamp(28px, 2.6vw, 39px);
            line-height: 1.12;
            letter-spacing: -.04em;
        }

        .intro-copy {
            margin-bottom: 28px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .feature-list,
        .role-list {
            display: grid;
            gap: 11px;
        }

        .feature-item,
        .role-item {
            min-width: 0;
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 11px;
            align-items: center;
            padding: 12px;
            background: rgba(255, 255, 255, .90);
            border: 1px solid rgba(221, 227, 236, .95);
            border-radius: 11px;
            box-shadow: 0 5px 15px rgba(31, 42, 61, .035);
            transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease;
        }

        .feature-item:hover,
        .role-item:hover {
            border-color: #c9d8ef;
            box-shadow: 0 10px 22px rgba(31, 42, 61, .075);
            transform: translateY(-2px);
        }

        .item-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            color: var(--blue);
            background: var(--blue-soft);
            border-radius: 10px;
        }

        .item-icon.teal {
            color: var(--teal);
            background: var(--teal-soft);
        }

        .item-icon.orange {
            color: var(--orange);
            background: var(--orange-soft);
        }

        .item-icon.violet {
            color: var(--violet);
            background: var(--violet-soft);
        }

        .item-icon svg,
        .input-icon,
        .security-note svg,
        .submit-button svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .feature-item strong,
        .role-item strong {
            display: block;
            margin-bottom: 3px;
            color: #394252;
            font-size: 11px;
        }

        .feature-item > div > span,
        .role-item > div > span {
            display: block;
            color: #8a93a1;
            font-size: 9.5px;
            line-height: 1.45;
        }

        .intro-footnote {
            margin: auto 0 0;
            padding-top: 24px;
            display: flex;
            align-items: center;
            gap: 9px;
            color: #737d8d;
            font-size: 10px;
            font-weight: 650;
        }

        .intro-footnote i,
        .online-chip i {
            width: 8px;
            height: 8px;
            flex: 0 0 auto;
            background: #22b573;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(34, 181, 115, .10);
        }

        .login-center {
            position: relative;
            min-width: 0;
            padding: 46px clamp(34px, 4vw, 64px);
            display: grid;
            place-items: center;
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(207, 214, 225, .92);
            border-radius: 20px;
            box-shadow: 0 22px 56px rgba(31, 42, 61, .13);
        }

        .login-center::before {
            background: linear-gradient(90deg, #2f6fdf, var(--blue), #70a0f5);
        }

        .login-center::after {
            content: "";
            position: absolute;
            right: -72px;
            bottom: -72px;
            width: 190px;
            height: 190px;
            background: radial-gradient(circle, rgba(57, 121, 237, .055) 0 34%, transparent 35% 100%);
            pointer-events: none;
        }

        .login-card {
            position: relative;
            z-index: 1;
            width: min(410px, 100%);
        }

        .login-welcome-icon {
            width: 50px;
            height: 50px;
            margin-bottom: 20px;
            display: grid;
            place-items: center;
            color: #fff;
            background: linear-gradient(145deg, #2f6fdf, #5b8ff0);
            border: 5px solid #edf3ff;
            border-radius: 15px;
            box-shadow: 0 10px 22px rgba(57, 121, 237, .19);
        }

        .login-welcome-icon svg {
            width: 21px;
            height: 21px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .login-heading {
            margin-bottom: 29px;
        }

        .login-heading h2 {
            margin-bottom: 8px;
            font-size: 29px;
            line-height: 1.15;
            letter-spacing: -.03em;
        }

        .login-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
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
            color: #414a59;
            font-size: 11px;
            font-weight: 750;
        }

        .input-control {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 13px;
            z-index: 1;
            color: #9aa3b1;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .input-control input {
            width: 100%;
            min-height: 46px;
            padding: 0 44px 0 42px;
            color: var(--text);
            background: #fff;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            outline: 0;
            font: 500 12px/1.4 "SIAMI Jakarta", sans-serif;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .input-control input::placeholder {
            color: #afb6c1;
        }

        .input-control input:focus {
            background: #fcfdff;
            border-color: #9ab8f1;
            box-shadow: 0 0 0 3px rgba(57, 121, 237, .10);
        }

        .input-control:focus-within .input-icon {
            color: var(--blue);
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 7px;
            width: 34px;
            height: 34px;
            padding: 0;
            display: grid;
            place-items: center;
            color: #8f98a7;
            background: transparent;
            border: 0;
            border-radius: 8px;
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
            width: 17px;
            height: 17px;
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
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #737d8c;
            font-size: 10.5px;
            cursor: pointer;
        }

        .remember input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        .remember-box {
            width: 18px;
            height: 18px;
            display: grid;
            place-items: center;
            color: #fff;
            background: #fff;
            border: 1px solid #cbd3df;
            border-radius: 5px;
            transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
        }

        .remember-box svg {
            width: 12px;
            height: 12px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.4;
            opacity: 0;
        }

        .remember input:checked + .remember-box {
            background: var(--blue);
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(57, 121, 237, .10);
        }

        .remember input:checked + .remember-box svg {
            opacity: 1;
        }

        .submit-button {
            min-height: 46px;
            padding: 0 17px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            color: #fff;
            background: linear-gradient(135deg, #2f6fdf, #4d86ef);
            border: 1px solid #2f6fdf;
            border-radius: 10px;
            box-shadow: 0 8px 18px rgba(57, 121, 237, .18);
            font: 750 12px/1 "SIAMI Jakarta", sans-serif;
            cursor: pointer;
            transition: transform .16s ease, background .16s ease, box-shadow .16s ease;
        }

        .submit-button:hover,
        .submit-button:focus-visible {
            background: #2f6fdf;
            box-shadow: 0 12px 24px rgba(57, 121, 237, .24);
            transform: translateY(-1px);
            outline: 0;
        }

        .error {
            padding: 8px 10px;
            color: #ad4851;
            background: #fff4f5;
            border: 1px solid #f1d3d6;
            border-radius: 8px;
            font-size: 10px;
            line-height: 1.45;
        }

        .login-security-line {
            margin: 22px 0 0;
            padding-top: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            color: #9aa2af;
            border-top: 1px solid #edf0f4;
            font-size: 9.5px;
        }

        .login-security-line svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: #6b93dc;
            stroke-width: 1.8;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 24px;
        }

        .panel-head h2 {
            margin: 0;
            font-size: 21px;
            letter-spacing: -.03em;
        }

        .online-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 9px;
            color: #21845b;
            background: #fff;
            border: 1px solid #d9e9e1;
            border-radius: 999px;
            font-size: 8.5px;
            font-weight: 750;
        }

        .online-chip i {
            width: 6px;
            height: 6px;
            box-shadow: none;
        }

        .role-intro {
            margin: -12px 0 20px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.6;
        }

        .security-note {
            margin-top: auto;
            padding: 16px;
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 10px;
            color: #526072;
            background: rgba(255, 255, 255, .82);
            border: 1px solid #dfe8e5;
            border-radius: 12px;
        }

        .security-note > span {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            color: var(--teal);
            background: var(--teal-soft);
            border-radius: 9px;
        }

        .security-note strong {
            display: block;
            margin-bottom: 4px;
            color: #344252;
            font-size: 10px;
        }

        .security-note p {
            margin: 0;
            color: #86918f;
            font-size: 9px;
            line-height: 1.55;
        }

        .copyright {
            margin: 15px 0 0;
            color: #a0a7b1;
            font-size: 8.5px;
            text-align: center;
        }

        @media (max-width: 1120px) {
            body {
                padding: 18px;
                place-items: start center;
            }

            .login-shell {
                grid-template-columns: minmax(310px, .88fr) minmax(390px, 1.12fr);
            }

            .role-panel {
                grid-column: 1 / -1;
                display: grid;
                grid-template-columns: minmax(220px, .65fr) minmax(0, 1.35fr);
                gap: 22px;
                border: 1px solid rgba(207, 214, 225, .90);
            }

            .role-panel .role-list {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .security-note {
                margin-top: 0;
                align-self: end;
            }

            .copyright {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 760px) {
            body {
                padding: 12px;
                background: var(--page);
            }

            body::before,
            body::after {
                display: none;
            }

            .login-shell {
                min-height: 100vh;
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .login-center {
                grid-row: 1;
                min-height: calc(100vh - 24px);
                padding: 36px 24px;
            }

            .mobile-brand {
                display: block;
                margin: 0 auto 28px;
            }

            .intro-panel,
            .role-panel {
                padding: 34px 24px;
                border: 1px solid rgba(207, 214, 225, .90);
            }

            .intro-panel .brand-lockup-crop {
                display: none;
            }

            .role-panel {
                grid-column: auto;
                display: flex;
            }

            .role-panel .role-list {
                grid-template-columns: minmax(0, 1fr);
            }

            .security-note {
                margin-top: 24px;
            }
        }

        @media (max-width: 420px) {
            .login-center {
                padding: 28px 18px;
            }

            .form-options {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <aside class="side-panel intro-panel" aria-label="Tentang SMART SIAMI">
            <div class="brand-lockup-crop">
                <img class="brand-lockup-image" src="{{ asset('images/brand/smart-siami-lockup.png') }}" alt="SMART SIAMI">
            </div>

            <span class="eyebrow">Platform mutu internal</span>
            <h1>Audit mutu yang terarah, transparan, dan terukur.</h1>
            <p class="intro-copy">SMART SIAMI menyatukan seluruh proses Audit Mutu Internal, mulai dari persiapan instrumen hingga pemantauan tindak lanjut.</p>

            <div class="feature-list">
                <article class="feature-item">
                    <span class="item-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"></rect><path d="M8 8h8M8 12h8M8 16h5"></path></svg>
                    </span>
                    <div><strong>Audit terintegrasi</strong><span>Instrumen, bukti, penilaian, dan temuan dalam satu ruang kerja.</span></div>
                </article>
                <article class="feature-item">
                    <span class="item-icon teal">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"></path></svg>
                    </span>
                    <div><strong>Monitoring real-time</strong><span>Pantau progres unit dan status audit secara cepat dan akurat.</span></div>
                </article>
                <article class="feature-item">
                    <span class="item-icon orange">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6M8 13h8M8 17h6"></path></svg>
                    </span>
                    <div><strong>Laporan siap pakai</strong><span>Rekap hasil audit dan tindak lanjut tersusun konsisten.</span></div>
                </article>
            </div>

            <p class="intro-footnote"><i></i> Sistem tersedia untuk mendukung siklus penjaminan mutu institusi.</p>
        </aside>

        <section class="login-center">
            <div class="login-card">
                <div class="brand-lockup-crop mobile-brand">
                    <img class="brand-lockup-image" src="{{ asset('images/brand/smart-siami-lockup.png') }}" alt="SMART SIAMI">
                </div>

                <header class="login-heading">
                    <span class="login-welcome-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle><path d="m16.5 11.5 1.5 1.5 3-3"></path></svg>
                    </span>
                    <span class="eyebrow">Portal akses</span>
                    <h2>Selamat datang kembali</h2>
                    <p>Masukkan akun institusi Anda untuk melanjutkan ke workspace SMART SIAMI.</p>
                </header>

                <form class="login-form" method="post" action="{{ route('login.store') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email</label>
                        <div class="input-control">
                            <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"></path><path d="m4 6 8 6 8-6"></path></svg>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@institusi.ac.id" autocomplete="email" autofocus required>
                        </div>
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Kata sandi</label>
                        <div class="input-control password-control">
                            <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                            <input id="password" name="password" type="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
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

                <p class="login-security-line">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
                    Koneksi aman dan akses dilindungi autentikasi akun
                </p>
            </div>
        </section>

        <aside class="side-panel role-panel" aria-label="Peran pengguna SMART SIAMI">
            <div>
                <div class="panel-head">
                    <h2>Akses sesuai peran</h2>
                    <span class="online-chip"><i></i> Online</span>
                </div>
                <p class="role-intro">Setiap pengguna mendapatkan workspace dan wewenang sesuai tanggung jawabnya.</p>
            </div>

            <div class="role-list">
                <article class="role-item">
                    <span class="item-icon violet">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0M18 4l1 1 2-2"></path></svg>
                    </span>
                    <div><strong>Administrator</strong><span>Mengelola master data, penugasan, monitoring, dan laporan.</span></div>
                </article>
                <article class="role-item">
                    <span class="item-icon teal">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"></path></svg>
                    </span>
                    <div><strong>Auditor</strong><span>Memeriksa bukti, menilai kesesuaian, dan memverifikasi tindak lanjut.</span></div>
                </article>
                <article class="role-item">
                    <span class="item-icon orange">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path><path d="M9 8h1M14 8h1M9 12h1M14 12h1"></path></svg>
                    </span>
                    <div><strong>Auditee</strong><span>Mengisi evaluasi diri, melengkapi bukti, dan menyelesaikan temuan.</span></div>
                </article>
            </div>

            <div class="security-note">
                <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg></span>
                <div><strong>Keamanan akun</strong><p>Jangan membagikan kata sandi. Hubungi administrator apabila mengalami kendala akses.</p></div>
            </div>

            <p class="copyright">&copy; {{ date('Y') }} SMART SIAMI &middot; Sistem Informasi Audit Mutu Internal</p>
        </aside>
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
