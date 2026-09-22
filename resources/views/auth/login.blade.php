<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="baseurl" content="{{ asset('') }}">

    <title>{{ config('app.name', 'NBS - OPUS BILLING') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    @include('includes.style')

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow: hidden;
        }

        /* ===== FULL-BLEED BACKGROUND ===== */
        .login-page {
            position: relative;
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            overflow: hidden;
        }

        .bg-image {
            position: absolute;
            inset: 0;
            background: url('{{ asset("assets/images/background/port-bg.jpg") }}') center center / cover no-repeat;
            z-index: 0;
            animation: slowZoom 30s ease-in-out infinite alternate;
        }

        @keyframes slowZoom {
            0% {
                transform: scale(1);
            }

            100% {
                transform: scale(1.08);
            }
        }

        /* Gradient overlays */
        .bg-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                linear-gradient(135deg, rgba(2, 6, 23, 0.82) 0%, rgba(2, 6, 23, 0.45) 50%, rgba(2, 6, 23, 0.70) 100%);
        }

        .bg-vignette {
            position: absolute;
            inset: 0;
            z-index: 1;
            background: radial-gradient(ellipse at center, transparent 40%, rgba(0, 0, 0, 0.5) 100%);
        }

        /* ===== BRANDING (Left Overlay) ===== */
        .brand-overlay {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 55%;
            z-index: 3;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 80px;
            pointer-events: none;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 40px;
        }

        .brand-logo img {
            height: 50px;
            width: auto;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
        }

        .brand-logo-text {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
        }

        .brand-tagline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background: rgba(14, 165, 233, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 50px;
            color: #7dd3fc;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 28px;
            backdrop-filter: blur(8px);
        }

        .brand-title {
            font-size: 42px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.15;
            margin: 0 0 18px 0;
            letter-spacing: -1px;
        }

        .brand-title span {
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-desc {
            font-size: 15px;
            color: rgba(203, 213, 225, 0.85);
            line-height: 1.7;
            max-width: 460px;
            margin-bottom: 40px;
        }

        .brand-features {
            display: flex;
            gap: 32px;
        }

        .brand-feat {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(226, 232, 240, 0.8);
            font-size: 13px;
            font-weight: 500;
        }

        .brand-feat-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: rgba(56, 189, 248, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #38bdf8;
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ===== LOGIN CARD (Right) ===== */
        .login-card {
            position: relative;
            z-index: 5;
            width: 440px;
            margin-right: 6%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(40px) saturate(1.5);
            -webkit-backdrop-filter: blur(40px) saturate(1.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 48px 40px;
            box-shadow:
                0 32px 64px -12px rgba(0, 0, 0, 0.55),
                0 0 0 1px rgba(255, 255, 255, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            animation: cardSlideIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes cardSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            text-align: center;
            margin-bottom: 36px;
        }

        .card-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 6px 0;
        }

        .card-header p {
            font-size: 14px;
            color: #94a3b8;
            margin: 0;
            font-weight: 400;
        }

        /* Input fields */
        .field {
            margin-bottom: 22px;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .field-input-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 20px;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .field-input {
            width: 100%;
            height: 52px;
            background: rgba(30, 41, 59, 0.6) !important;
            border: 1.5px solid rgba(100, 116, 139, 0.3) !important;
            border-radius: 14px !important;
            padding: 0 48px 0 48px !important;
            color: #f1f5f9 !important;
            font-size: 14px !important;
            font-family: inherit !important;
            font-weight: 500 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .field-input::placeholder {
            color: #475569 !important;
            font-weight: 400 !important;
        }

        .field-input:focus {
            background: rgba(30, 41, 59, 0.85) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.12), 0 4px 16px rgba(56, 189, 248, 0.08) !important;
        }

        .field-input:focus~.field-icon {
            color: #38bdf8;
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #475569;
            cursor: pointer;
            padding: 4px;
            font-size: 20px;
            transition: color 0.2s ease;
            line-height: 1;
        }

        .toggle-pw:hover {
            color: #e2e8f0;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 14px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            letter-spacing: 0.5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 50%, #38bdf8 100%);
            background-size: 200% 200%;
            animation: gradientShift 4s ease infinite;
            box-shadow: 0 8px 24px -4px rgba(14, 165, 233, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px -4px rgba(14, 165, 233, 0.55);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Status indicator */
        .sys-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 28px;
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        .sys-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.6);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 4px rgba(34, 197, 94, 0.4);
            }

            50% {
                box-shadow: 0 0 12px rgba(34, 197, 94, 0.8);
            }
        }

        .card-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 28px 0 24px;
        }

        .card-divider::before,
        .card-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(100, 116, 139, 0.3), transparent);
        }

        .card-divider span {
            font-size: 11px;
            color: #475569;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Footer */
        .page-footer {
            position: absolute;
            bottom: 24px;
            left: 0;
            right: 0;
            z-index: 3;
            text-align: center;
            color: rgba(148, 163, 184, 0.5);
            font-size: 12px;
            font-weight: 500;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) {
            .brand-overlay {
                width: 50%;
                padding: 40px 48px;
            }

            .brand-title {
                font-size: 32px;
            }

            .brand-features {
                flex-direction: column;
                gap: 14px;
            }
        }

        @media (max-width: 860px) {
            .brand-overlay {
                display: none;
            }

            .login-page {
                justify-content: center;
            }

            .login-card {
                margin-right: 0;
                width: 92%;
                max-width: 420px;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 36px 24px;
                border-radius: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
        </svg>
    </div>

    <div class="login-page">
        <!-- Full-bleed background -->
        <div class="bg-image"></div>
        <div class="bg-overlay"></div>
        <div class="bg-vignette"></div>

        <!-- Left branding overlay -->
        <div class="brand-overlay">
            <div class="brand-logo">
                @if(file_exists(public_path('assets/images/MTI-Logo1.png')))
                    <img src="{{ asset('assets/images/MTI-Logo1.png') }}" alt="MTI Logo">
                @elseif(file_exists(public_path('assets/images/MTI-Logo.png')))
                    <img src="{{ asset('assets/images/MTI-Logo.png') }}" alt="MTI Logo">
                @endif
                <span class="brand-logo-text">NBS</span>
            </div>

            <div class="brand-tagline">
                <i class="mdi mdi-anchor"></i>
                NBS NEW BILLING SYSTEM
            </div>

            <h1 class="brand-title">
                Terminal Billing <br><span>Kepelabuhanan</span> <br>Terpadu
            </h1>

            <p class="brand-desc">
                Sistem pengelolaan nota billing, operasional terminal petikemas, dan layanan kepelabuhanan PT Multi
                Terminal Indonesia.
            </p>


        </div>

        <!-- Login card -->
        <div class="login-card">
            <div class="card-header">
                <h2>Selamat Datang</h2>
                <p>Masuk ke sistem NBS Billing</p>
            </div>

            <div class="card-divider"><span>Login</span></div>

            <!-- Form ID & input names/IDs 100% sama — JS login.js tetap berjalan -->
            <form id="loginform" action="javascript:void(0)" novalidate>
                @csrf

                <div class="field">
                    <label for="username">Username</label>
                    <div class="field-input-wrap">
                        <i class="mdi mdi-account-outline field-icon"></i>
                        <input class="field-input" type="text" required name="username" id="username"
                            placeholder="Masukkan username" autocomplete="username">
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="field-input-wrap">
                        <i class="mdi mdi-lock-outline field-icon"></i>
                        <input class="field-input" type="password" required name="password" id="password"
                            placeholder="••••••••" autocomplete="current-password">
                        <button type="button" class="toggle-pw" id="togglePasswordBtn" title="Show/Hide Password">
                            <i class="mdi mdi-eye-outline" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button class="btn-login" type="submit">
                    <span>MASUK</span>
                    <i class="mdi mdi-arrow-right"></i>
                </button>
            </form>

            <div class="sys-status">
                <span class="sys-dot"></span>
                <span>System Active</span>
            </div>
        </div>

        <div class="page-footer">
            &copy; {{ date('Y') }} PT Multi Terminal Indonesia &mdash; NBS v2.5
        </div>
    </div>

    @include('includes.script')
    <script src="{{ asset('pages/auth/login.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggleBtn = document.getElementById('togglePasswordBtn');
            var passwordInput = document.getElementById('password');
            var eyeIcon = document.getElementById('eyeIcon');

            if (toggleBtn && passwordInput && eyeIcon) {
                toggleBtn.addEventListener('click', function () {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        eyeIcon.classList.remove('mdi-eye-outline');
                        eyeIcon.classList.add('mdi-eye-off-outline');
                    } else {
                        passwordInput.type = 'password';
                        eyeIcon.classList.remove('mdi-eye-off-outline');
                        eyeIcon.classList.add('mdi-eye-outline');
                    }
                });
            }
        });
    </script>
</body>

</html>