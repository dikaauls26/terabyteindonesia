@extends('layouts.guest')
@section('title', 'Daftar')
@section('content')
    <div class="mb-3">
        <h4 class="mb-1">Buat Akun Baru ✨</h4>
        <div class="text-muted">Akses dashboard pelanggan & penagihan dalam hitungan detik.</div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> Periksa kembali input Anda.</div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Email</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
            </div>
            <div class="col-md-12">
                <label class="form-label">Kata Sandi</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="pwd1" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePwd1"><i
                            class="bi bi-eye"></i></button>
                </div>
                <div class="form-text">Gunakan min. 8 karakter campuran huruf & angka.</div>
            </div>
            <div class="col-md-12">
                <label class="form-label">Ulangi Kata Sandi</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password_confirmation" id="pwd2" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePwd2"><i
                            class="bi bi-eye"></i></button>
                </div>
            </div>
        </div>

        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="agree" required>
            <label class="form-check-label" for="agree">Saya menyetujui kebijakan privasi & ketentuan layanan.</label>
        </div>

        <button class="btn btn-primary btn-lg w-100 mt-3" type="submit">
            <i class="bi bi-person-plus me-1"></i> Buat Akun
        </button>

        <div class="text-center small mt-2">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
        <div class="alert alert-light border mt-3 small">
            <i class="bi bi-shield-lock me-1"></i> Data Anda dilindungi. Kami tidak membagikan informasi Anda ke pihak
            ketiga.
        </div>
    </form>

    @push('scripts')
        <script>
            function hook(btnId, inputId) {
                const b = document.getElementById(btnId), i = document.getElementById(inputId);
                b?.addEventListener('click', () => {
                    i.type = i.type === 'password' ? 'text' : 'password';
                    b.innerHTML = i.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
                });
            }
            hook('togglePwd1', 'pwd1'); hook('togglePwd2', 'pwd2');
        </script>
    @endpush
@endsection