@php
    // Taruh file logo di: public/images/logo.png
    // Taruh ilustrasi (opsional) di: public/images/login-illustration.png
    $hasLogo = file_exists(public_path('images/logo.png'));
    $hasIllustration = file_exists(public_path('images/login-illustration.png'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Administrator · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: #C9E2FD;
            color: #1F2937;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .auth-card {
            width: 100%;
            max-width: 980px;
            min-height: 600px;
            display: flex;
            background: #FFFFFF;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 60px -20px rgba(37, 99, 235, 0.35);
        }

        /* ===== Panel kiri (biru) ===== */
        .auth-hero {
            flex: 1;
            position: relative;
            background: #6AAFFA;
            color: #FFFFFF;
            padding: 36px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand-logo {
            width: 34px; height: 34px; border-radius: 50%;
            background: #FFFFFF; color: #3B82F6;
            display: grid; place-items: center;
            font-size: 11px; font-weight: 800; letter-spacing: -0.5px;
            overflow: hidden;
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: contain; }
        .brand-name { font-size: 16px; font-weight: 700; }
        .brand-tagline { margin-top: 14px; font-size: 13px; line-height: 1.55; color: rgba(255, 255, 255, 0.85); max-width: 260px; }

        .hero-art { flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px 0; }
        .hero-art img { max-width: 100%; max-height: 340px; object-fit: contain; }
        .hero-art svg { width: 100%; max-width: 360px; height: auto; }

        .hero-footer { font-size: 13px; color: rgba(255, 255, 255, 0.85); }
        .hero-footer strong { color: #FFFFFF; font-weight: 700; }

        /* ===== Panel kanan (form) ===== */
        .auth-form {
            flex: 1;
            padding: 36px 56px;
            display: flex;
            flex-direction: column;
        }
        .form-top { display: flex; justify-content: flex-end; font-size: 12px; color: #9CA3AF; }
        .form-body { flex: 1; display: flex; flex-direction: column; justify-content: center; max-width: 340px; width: 100%; margin: 0 auto; }
        .form-title { font-size: 34px; font-weight: 700; color: #111827; letter-spacing: -0.5px; }
        .form-subtitle { margin-top: 6px; margin-bottom: 26px; font-size: 13px; color: #6B7280; line-height: 1.5; }

        .field { margin-bottom: 16px; }
        .field label { display: block; margin-bottom: 7px; font-size: 13px; font-weight: 600; color: #111827; }
        .input-wrap { position: relative; }
        .field-input {
            width: 100%;
            height: 42px;
            padding: 0 14px;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            background: #FFFFFF;
            font-family: inherit;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-input::placeholder { color: #B0B7C3; }
        .field-input:focus { border-color: #60A5FA; box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2); }
        .field-input.flash { border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3); }
        .has-toggle { padding-right: 42px; }
        .toggle-btn {
            position: absolute; top: 0; right: 0; height: 100%; width: 42px;
            display: grid; place-items: center;
            border: 0; background: transparent; color: #9CA3AF; cursor: pointer;
        }
        .toggle-btn:hover { color: #4B5563; }
        .toggle-btn svg { width: 16px; height: 16px; }
        .hidden { display: none !important; }

        .remember { display: flex; align-items: center; gap: 8px; margin: 4px 0 22px; font-size: 12px; color: #6B7280; cursor: pointer; user-select: none; }
        .remember input { width: 14px; height: 14px; accent-color: #3B82F6; }

        .btn-primary {
            width: 100%; height: 42px;
            border: 0; border-radius: 6px;
            background: #5B9DFA; color: #FFFFFF;
            font-family: inherit; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: background .15s, transform .1s;
        }
        .btn-primary:hover { background: #3B82F6; }
        .btn-primary:active { transform: scale(0.99); }

        .divider { display: flex; align-items: center; gap: 12px; margin: 22px 0 16px; font-size: 12px; color: #9CA3AF; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #F0F1F3; }

        .demo-btn {
            width: 100%;
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            padding: 10px 14px;
            border: 1px solid #E5E7EB; border-radius: 6px;
            background: #FFFFFF; cursor: pointer;
            font-family: inherit; text-align: left;
            transition: border-color .15s, background .15s;
        }
        .demo-btn:hover { border-color: #93C5FD; background: #F5F9FF; }
        .demo-email { font-size: 13px; font-weight: 600; color: #111827; }
        .demo-hint { font-size: 11px; color: #9CA3AF; margin-top: 2px; }
        .demo-action { font-size: 12px; font-weight: 600; color: #3B82F6; white-space: nowrap; }

        .alert {
            margin-bottom: 18px; padding: 12px 14px;
            border: 1px solid #FECACA; border-radius: 6px;
            background: #FEF2F2; color: #B91C1C; font-size: 12px;
        }
        .alert strong { display: block; margin-bottom: 4px; }
        .alert ul { padding-left: 16px; }

        .form-footer { text-align: center; font-size: 12px; color: #9CA3AF; }

        @media (max-width: 860px) {
            .auth-hero { display: none; }
            .auth-form { padding: 32px 24px; }
            .auth-card { min-height: auto; }
        }
    </style>
</head>
<body>
    <div class="auth-card">

        <!-- PANEL KIRI: Branding & Ilustrasi -->
        <aside class="auth-hero">
            <div>
                <div class="brand">
                    <div class="brand-logo">
                        @if ($hasLogo)
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
                        @else
                            DSC
                        @endif
                    </div>
                    <span class="brand-name">{{ config('app.name') }}</span>
                </div>
                <p class="brand-tagline">Pusat operasional hardware, lisensi software, dan layanan IT terintegrasi.</p>
            </div>

            <div class="hero-art">
                @if ($hasIllustration)
                    <img src="{{ asset('images/login-illustration.png') }}" alt="Ilustrasi">
                @else
                    <svg viewBox="0 0 360 300" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="70" cy="60" r="12" fill="#F4B6C2"/>
                        <path d="M140 20 L170 75 L120 70 Z" fill="#B8A4F5"/>
                        <rect x="210" y="35" width="90" height="16" rx="8" fill="#E8F1FF"/>
                        <rect x="220" y="60" width="90" height="16" rx="8" fill="#E8F1FF"/>
                        <rect x="210" y="85" width="90" height="16" rx="8" fill="#E8F1FF"/>
                        <rect x="85" y="95" width="160" height="150" rx="16" fill="#9CC8FB"/>
                        <rect x="105" y="115" width="120" height="110" rx="10" stroke="#3B5BDB" stroke-width="5"/>
                        <circle cx="105" cy="115" r="9" fill="#EF4444"/>
                        <circle cx="225" cy="115" r="9" fill="#EF4444"/>
                        <circle cx="105" cy="225" r="9" fill="#EF4444"/>
                        <circle cx="225" cy="225" r="9" fill="#EF4444"/>
                        <rect x="125" y="135" width="45" height="50" rx="8" fill="#4C6EF5"/>
                        <circle cx="147" cy="158" r="12" fill="#FFFFFF"/>
                        <rect x="180" y="150" width="35" height="12" rx="6" fill="#B8A4F5"/>
                        <path d="M165 200 L165 245 L177 233 L188 255 L196 251 L185 229 L201 229 Z" fill="#FFFFFF"/>
                        <circle cx="300" cy="150" r="18" fill="#B8A4F5"/>
                        <circle cx="45" cy="230" r="16" fill="#B8A4F5"/>
                        <path d="M40 140 Q20 165 45 185" stroke="#F4B6C2" stroke-width="12" stroke-linecap="round"/>
                        <rect x="230" y="235" width="80" height="12" rx="6" fill="#F4B6C2" transform="rotate(-12 230 235)"/>
                        <rect x="285" y="105" width="14" height="14" rx="3" fill="#4C6EF5"/>
                    </svg>
                @endif
            </div>

            <p class="hero-footer">Butuh bantuan akun? <strong>Hubungi Support IT</strong></p>
        </aside>

        <!-- PANEL KANAN: Form Login -->
        <main class="auth-form">
            <div class="form-top">Bahasa Indonesia</div>

            <div class="form-body">
                <h1 class="form-title">Masuk</h1>
                <p class="form-subtitle">Akses panel admin untuk mengelola katalog, transaksi, dan layanan pelanggan.</p>

                @if ($errors->any())
                    <div class="alert">
                        <strong>Gagal mengakses akun</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Alamat Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="nama@itselling.test" class="field-input">
                    </div>

                    <div class="field">
                        <label for="password">Kata Sandi</label>
                        <div class="input-wrap">
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                placeholder="•••• •••• ••••" class="field-input has-toggle">
                            <button type="button" id="toggle-password" class="toggle-btn" title="Lihat kata sandi">
                                <svg id="eye-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg id="eye-off-icon" class="hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <label class="remember">
                        <input type="checkbox" name="remember">
                        <span>Ingat sesi di perangkat ini</span>
                    </label>

                    <button type="submit" id="btn-submit" class="btn-primary">Masuk ke Panel Admin</button>
                </form>
            </div>

            <p class="form-footer">&copy; {{ now()->year }} {{ config('app.name') }}</p>
        </main>
    </div>

    <script>
        // Password Visibility Toggle
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('toggle-password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        toggleBtn?.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        });

    </script>
</body>
</html>
