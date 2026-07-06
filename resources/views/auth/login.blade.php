<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login SMART SIAMI</title>
    <style>
        :root {
            --bg: #0b1f1b;
            --surface: rgba(18, 46, 40, .86);
            --surface-soft: rgba(24, 63, 55, .72);
            --line: rgba(34, 211, 238, .16);
            --line-strong: rgba(45, 212, 191, .28);
            --text: #e2f5f0;
            --muted: #9cc9bf;
            --brand: #0e6656;
            --brand-strong: #14967d;
            --brand-soft: rgba(45, 212, 191, .12);
            --brand-cyan: #22d3ee;
            --secondary: #2dd4bf;
            --danger: #ef4444;
            --shadow-lg: 0 26px 64px rgba(0, 0, 0, .36), 0 0 44px rgba(34, 211, 238, .13);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                linear-gradient(rgba(34, 211, 238, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, .035) 1px, transparent 1px),
                radial-gradient(circle at 18% 18%, rgba(45, 212, 191, .18), transparent 30vw),
                radial-gradient(circle at 82% 12%, rgba(34, 211, 238, .14), transparent 28vw),
                radial-gradient(circle at 80% 78%, rgba(20, 150, 125, .18), transparent 32vw),
                var(--bg);
            background-size: 42px 42px, 42px 42px, auto, auto, auto, auto;
            color: var(--text);
            font-family: Inter, Poppins, "Segoe UI", Arial, Helvetica, sans-serif;
            text-rendering: optimizeLegibility;
        }

        .login-panel {
            width: min(100%, 420px);
            background: var(--surface);
            border: 1px solid rgba(34, 211, 238, .20);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(12px);
        }

        .login-panel::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 6px;
            background: linear-gradient(90deg, #0a4a3f, #0e6656, #14967d, #22d3ee);
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0e6656, #14967d, #22d3ee);
            color: #ecfffb;
            font-weight: 900;
            box-shadow: 0 0 26px rgba(34, 211, 238, .22);
        }

        h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 0;
            line-height: 1;
            color: #f0fffb;
            font-family: Poppins, Inter, "Segoe UI", sans-serif;
            font-weight: 900;
        }

        .subtitle {
            margin: 6px 0 24px;
            color: var(--muted);
        }

        form {
            display: grid;
            gap: 16px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        label {
            font-size: 14px;
            font-weight: 700;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1px solid rgba(34, 211, 238, .18);
            border-radius: 14px;
            padding: 11px 12px;
            color: var(--text);
            background: rgba(11, 31, 27, .74);
            font: inherit;
            box-shadow: inset 0 1px 1px rgba(15, 23, 42, .03);
            transition: border-color .16s ease, box-shadow .16s ease;
        }

        input:focus {
            outline: 3px solid rgba(34, 211, 238, .20);
            border-color: var(--brand-cyan);
            box-shadow: 0 0 0 1px rgba(34, 211, 238, .16), 0 0 20px rgba(34, 211, 238, .10);
        }

        input[type="checkbox"] {
            accent-color: var(--brand-cyan);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 14px;
        }

        button {
            border: 0;
            border-radius: 14px;
            padding: 12px 14px;
            background: linear-gradient(135deg, #0e6656, #14967d, #22d3ee);
            color: #ecfffb;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 0 0 1px rgba(34, 211, 238, .10), 0 10px 24px rgba(34, 211, 238, .14);
            transition: background .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        button:hover {
            background: linear-gradient(135deg, #14967d, #2dd4bf, #22d3ee);
            transform: translateY(-1px) scale(1.02);
            box-shadow: 0 0 0 1px rgba(34, 211, 238, .22), 0 14px 32px rgba(34, 211, 238, .30);
        }

        .error {
            color: #fecdd3;
            font-size: 13px;
        }

        /* Calm JDS login theme */
        :root {
            --bg: #fafaf8;
            --surface: #ffffff;
            --line: #e5e7e0;
            --line-strong: #d8ddd4;
            --text: #1f2c29;
            --muted: #6b7b76;
            --brand: #0e6656;
            --brand-strong: #0a4a3f;
            --brand-soft: #e4f2ee;
            --secondary: #3d9c87;
            --accent: #e8b36a;
            --danger: #c7645a;
            --shadow-lg: 0 18px 42px rgba(14, 102, 86, .14);
        }

        body {
            background:
                linear-gradient(135deg, rgba(228, 242, 238, .75), transparent 34vw),
                linear-gradient(315deg, rgba(232, 179, 106, .12), transparent 30vw),
                var(--bg);
            background-size: auto;
            color: var(--text);
            font-family: Inter, Manrope, "Segoe UI", Arial, Helvetica, sans-serif;
        }

        .login-panel {
            background: #ffffff;
            border: 1px solid var(--line);
            box-shadow: var(--shadow-lg);
            backdrop-filter: none;
        }

        .login-panel::before {
            background: linear-gradient(90deg, var(--brand-strong), var(--brand), var(--secondary));
        }

        .brand-mark {
            background: var(--brand-soft);
            color: var(--brand);
            box-shadow: inset 0 0 0 1px #cfe5de;
        }

        h1 {
            color: var(--text);
            font-family: "Plus Jakarta Sans", Manrope, Inter, "Segoe UI", sans-serif;
            font-weight: 800;
        }

        input[type="email"],
        input[type="password"] {
            background: #ffffff;
            border-color: var(--line-strong);
            color: var(--text);
        }

        input:focus {
            outline: 3px solid rgba(14, 102, 86, .14);
            border-color: var(--brand);
            box-shadow: 0 0 0 1px rgba(14, 102, 86, .08);
        }

        input[type="checkbox"] {
            accent-color: var(--brand);
        }

        button {
            background: var(--brand);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(14, 102, 86, .12);
        }

        button:hover {
            background: var(--secondary);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(14, 102, 86, .16);
        }

        .error {
            color: var(--danger);
        }

        .brand-mark {
            display: flex;
            width: 112px;
            height: 112px;
            margin: 0 auto 18px;
            background: transparent;
            padding: 0;
            box-shadow: none;
            border-radius: 0;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 0;
        }

        h1,
        .subtitle {
            text-align: center;
        }

        .subtitle {
            margin-bottom: 28px;
        }

        @media (max-width: 480px) {
            .brand-mark {
                width: 96px;
                height: 96px;
            }
        }
    </style>
</head>
<body>
    <section class="login-panel">
        <div class="brand-mark">
            <img src="{{ route('brand.logo.login') }}" alt="Logo JDS">
        </div>
        <h1>SMART SIAMI</h1>
        <p class="subtitle">Masuk untuk mengelola Audit Mutu Internal.</p>

        <form method="post" action="{{ route('login.store') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">Kata sandi</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <label class="remember">
                <input type="checkbox" name="remember" value="1">
                Ingat saya
            </label>

            <button type="submit">Masuk</button>
        </form>
    </section>
</body>
</html>
