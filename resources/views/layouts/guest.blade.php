<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') • {{ config('app.name', 'CRM WiFi') }}</title>

    <!-- Bootstrap & icons (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(1000px 500px at -10% -10%, #22c55e33, transparent),
                radial-gradient(800px 400px at 110% 0%, #0ea5e933, transparent),
                #0b1020;
        }

        .auth-wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem;
        }

        .auth-card {
            max-width: 980px;
            width: 100%;
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25)
        }

        .auth-side {
            background: #0f172a;
            color: #e2e8f0;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between
        }

        .auth-side .brand {
            display: flex;
            align-items: center;
            gap: .75rem
        }

        .auth-side .logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #22c55e;
            color: #0f172a;
            display: grid;
            place-items: center;
            font-weight: 800
        }

        .auth-side .bullets li {
            margin: .5rem 0
        }

        .auth-form {
            background: #ffffff;
            padding: 2rem
        }

        @media (max-width: 992px) {
            .auth-card {
                grid-template-columns: 1fr
            }

            .auth-side {
                display: none
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-side">
                <div>
                    <div class="brand mb-3">
                        <div class="logo">CW</div>
                        <div>
                            <div class="fw-bold text-white">{{ config('app.name', 'CRM WiFi') }}</div>
                            <div class="small" style="color:#94a3b8">Billing & Monitoring Pelanggan</div>
                        </div>
                    </div>
                    <h4 class="text-white">Kelola Tagihan Lebih Cepat</h4>
                    <ul class="bullets list-unstyled small mt-3">
                        <li><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Generate invoice bulanan
                            otomatis</li>
                        <li><i class="bi bi-whatsapp text-success me-1"></i> Kirim tagihan via WhatsApp & Email</li>
                        <li><i class="bi bi-shield-check text-info me-1"></i> Pembayaran aman via Midtrans</li>
                    </ul>
                </div>
                <div class="small" style="color:#94a3b8">© <span id="y"></span> {{ config('app.name', 'CRM WiFi') }}
                </div>
            </div>

            <div class="auth-form">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>document.getElementById('y')?.append(new Date().getFullYear());</script>
    @stack('scripts')
</body>

</html>