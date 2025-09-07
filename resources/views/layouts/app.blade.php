<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') • {{ config('app.name', 'CRM WiFi') }}</title>

    <!-- Bootstrap & friends (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.0.7/css/dataTables.bootstrap5.min.css"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">


    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #0f172a;
            /* slate-900 */
            --sidebar-hover: #1e293b;
            /* slate-800 */
            --accent: #22c55e;
            /* green-500 */
        }

        body {
            background: #f8fafc;
        }

        .app-shell {
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sidebar-bg);
            color: #cbd5e1;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, .06);
            z-index: 1030;
            transform: translateX(0);
            transition: transform .25s ease;
        }

        .sidebar .brand {
            height: 64px;
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: 0 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .sidebar .logo {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-weight: 800;
            color: #0f172a;
            background: var(--accent);
        }

        .sidebar .nav {
            padding: .75rem;
            overflow-y: auto;
        }

        .sidebar .nav .nav-link {
            color: #cbd5e1;
            border-radius: 10px;
            padding: .625rem .75rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .sidebar .nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar .nav .nav-link.active {
            color: #fff;
            background: rgba(34, 197, 94, .12);
            border: 1px solid rgba(34, 197, 94, .35);
        }

        .sidebar .section-title {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            padding: .5rem .9rem;
        }

        .sidebar .collapse .nav-link {
            padding-left: 2.25rem;
        }

        /* Content */
        .content-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .app-footer {
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: .75rem 1rem;
            font-size: .875rem;
            color: #64748b;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .content-wrap {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }
        }
    </style>

    @stack('styles')
</head>

<body class="app-shell">
    @php
        function nav_active($patterns)
        {
            foreach ((array) $patterns as $p) {
                if (request()->routeIs($p))
                    return 'active';
            }
            return '';
        }
      @endphp

    <aside class="sidebar">
        <div class="brand">
            <div class="logo">CW</div>
            <div>
                <div class="fw-bold text-white">{{ config('app.name', 'CRM WiFi') }}</div>
                <div class="small" style="color:#94a3b8">Billing & CRM</div>
            </div>
        </div>

        <div class="section-title">Main</div>
        <nav class="nav flex-column">
            <a class="nav-link {{ nav_active(['home', 'dashboard']) }}" href="{{ url('/home') }}">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>

            <a class="nav-link {{ nav_active(['customers.*']) }}" href="{{ route('customers.index') }}">
                <i class="bi bi-people"></i> <span>Customers</span>
            </a>

            <a class="nav-link {{ nav_active(['invoices.*']) }}" href="{{ route('invoices.index') }}">
                <i class="bi bi-receipt"></i> <span>Invoices</span>
            </a>

            {{-- Masters --}}
            @php
                $mastersOpen = request()->routeIs('sites.*') || request()->routeIs('plans.*') || request()->routeIs('vouchers.*');
              @endphp
            <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse"
                href="#masterMenu" role="button" aria-expanded="{{ $mastersOpen ? 'true' : 'false' }}"
                aria-controls="masterMenu">
                <span><i class="bi bi-sliders"></i><span class="ms-2">Masters</span></span>
                <i class="bi bi-chevron-down small"></i>
            </a>

            <div class="collapse {{ $mastersOpen ? 'show' : '' }}" id="masterMenu">
                <nav class="nav flex-column ps-2">
                    <a class="nav-link {{ nav_active(['sites.*']) }}" href="{{ route('sites.index') }}">
                        <i class="bi bi-geo-alt"></i> <span>Sites</span>
                    </a>
                    <a class="nav-link {{ nav_active(['plans.*']) }}" href="{{ route('plans.index') }}">
                        <i class="bi bi-hdd-network"></i> <span>Plans</span>
                    </a>
                    <a class="nav-link {{ nav_active(['vouchers.*']) }}" href="{{ route('vouchers.index') }}">
                        <i class="bi bi-ticket-perforated"></i> <span>Vouchers</span>
                    </a>
                </nav>
            </div>

            @auth
                <div class="section-title">Account</div>
                <a class="nav-link" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            @endauth
        </nav>

    </aside>

    <div class="content-wrap">
        <header class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-link d-lg-none p-0" id="btnSidebar"><i class="bi bi-list fs-4"></i></button>
                <div class="fs-6 fw-semibold">@yield('title', 'Dashboard')</div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <li class="breadcrumb-item active" aria-current="page">@yield('title', 'Dashboard')</li>
                    @endif
                </ol>
            </nav>
        </header>

        <main class="flex-grow-1">
            <div class="container-fluid py-3">
                @if(session('ok'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check2-circle me-1"></i> {{ session('ok') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> Terjadi kesalahan. Periksa input Anda.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="app-footer text-center">
            © <span id="y"></span> {{ config('app.name', 'CRM WiFi') }}. All rights reserved.
        </footer>
    </div>

    <!-- JS CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@2.0.7/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.0.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>

    <script>
        // Sidebar toggle for mobile (persist state)
        const btn = document.getElementById('btnSidebar');
        const key = 'sidebar:open';
        function applySidebar() {
            const open = localStorage.getItem(key) !== '0';
            if (window.matchMedia('(max-width: 992px)').matches) {
                document.body.classList.toggle('sidebar-open', open);
            } else {
                document.body.classList.remove('sidebar-open');
            }
        }
        function toggleSidebar() {
            const open = localStorage.getItem(key) !== '0';
            localStorage.setItem(key, open ? '0' : '1'); applySidebar();
        }
        btn?.addEventListener('click', toggleSidebar);
        window.addEventListener('resize', applySidebar);
        document.addEventListener('DOMContentLoaded', () => { document.getElementById('y').textContent = new Date().getFullYear(); applySidebar(); });
    </script>

    @stack('scripts')
</body>

</html>