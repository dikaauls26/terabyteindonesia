@extends('layouts.guest')
@section('title', 'Masuk')
@section('content')
    <div class="mb-3">
        <h4 class="mb-1">Selamat datang kembali 👋</h4>
        <div class="text-muted">Masuk untuk mengelola pelanggan & tagihan.</div>
    </div>

    @if(session('status'))
        <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i> {{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Email atau kata sandi tidak valid.</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <div class="input-group input-group-lg">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label d-flex justify-content-between align-items-center">Kata Sandi
                @if (Route::has('password.request'))
                    <a class="small" href="{{ route('password.request') }}">Lupa sandi?</a>
                @endif
            </label>
            <div class="input-group input-group-lg">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" id="pwd" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePwd"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">Ingat saya</label>
        </div>

        <button class="btn btn-success btn-lg w-100 mb-2" type="submit">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
        </button>

        @if (Route::has('register'))
            <div class="text-center small">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></div>
        @endif

        <div class="alert alert-light border mt-3 small">
            <i class="bi bi-shield-lock me-1"></i> Jangan bagikan kredensial Anda. Aktivitas login tercatat untuk keamanan.
        </div>
    </form>

    @push('scripts')
        <script>
            const t = document.getElementById('togglePwd');
            const p = document.getElementById('pwd');
            t?.addEventListener('click', () => {
                p.type = p.type === 'password' ? 'text' : 'password';
                t.innerHTML = p.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
            });
        </script>
    @endpush
@endsection