<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ config('app.name', 'CRM WiFi') }} — Internet Cepat untuk Semua</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --brand:#0d6efd; }
    .hero {
      background: radial-gradient(1200px 600px at 20% -10%, #e8f1ff, #fff);
      padding: 64px 0 48px;
    }
    .glass {
      background: rgba(255,255,255,.75);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(13,110,253,.15);
      border-radius: 16px;
    }
    .card-lite { border-radius: 14px; border: 1px solid #eee; }
    .badge-soft { background: #eef4ff; color: #0d6efd; }
    .shadow-soft { box-shadow: 0 12px 26px rgba(13,110,253,.08); }
    .icon-circle {
      width: 44px; height: 44px; border-radius: 50%;
      display: inline-flex; align-items:center; justify-content:center;
      background: #eef4ff; color: var(--brand);
    }
    footer { border-top:1px solid #f0f0f0; }
    @media (max-width: 576px) {
      .hero { padding: 48px 0 32px; }
    }
  </style>
</head>
<body>

  {{-- NAVBAR --}}
  <nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="/">
        <i class="bi bi-wifi me-2 text-primary"></i>{{ config('app.name', 'CRM WiFi') }}
      </a>
      <div class="d-flex">
        <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">Login Staff</a>
      </div>
    </div>
  </nav>

  {{-- HERO --}}
  <section class="hero">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <span class="badge badge-soft mb-2"><i class="bi bi-lightning-charge me-1"></i> Internet Gesit • Layanan Proaktif</span>
          <h1 class="fw-bold lh-sm mb-3">
            Internet rumahan & bisnis yang <span class="text-primary">stabil</span>, cepat, dan <span class="text-primary">terjangkau</span>.
          </h1>
          <p class="text-secondary mb-4">
            Kami menghadirkan koneksi yang andal untuk produktivitas tanpa drama. 
            Cek tagihan Anda, bayar dengan aman, atau beli voucher hotspot—semuanya di satu tempat.
          </p>
          <div class="d-flex gap-2">
            <a href="#cek-tagihan" class="btn btn-primary shadow-soft">
              <i class="bi bi-receipt me-1"></i> Cek Tagihan Saya
            </a>
            {{-- tombol ini buka modal voucher --}}
            <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#buyVoucherModal">
              <i class="bi bi-ticket-perforated me-1"></i> Beli Voucher Hotspot
            </a>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="glass p-3 shadow-soft">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTbosnjRwq4e8bb7292CTSIEDlNWtU-F8PCgWOO50Cqllgff2gxUfb78GKxLujY4ctBwUU&usqp=CAU" class="img-fluid" alt="Illustration">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- CEK TAGIHAN --}}
  <section id="cek-tagihan" class="py-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-5">
          <div class="card card-lite shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="icon-circle me-2"><i class="bi bi-receipt"></i></div>
                <div>
                  <div class="fw-semibold">Cek Tagihan Saya</div>
                  <small class="text-secondary">Masukkan <strong>email</strong> atau <strong>nomor pelanggan</strong> Anda.</small>
                </div>
              </div>

              @if(isset($error) && $error)
                <div class="alert alert-warning">{{ $error }}</div>
              @endif

              <form method="POST" action="{{ route('public.check-bills') }}" class="needs-validation" novalidate>
                @csrf
                <div class="mb-3">
                  <label class="form-label">Email / Nomor Pelanggan</label>
                  <input type="text" name="identifier" class="form-control @error('identifier') is-invalid @enderror"
                         placeholder="contoh: nama@email.com atau CUST-0001"
                         value="{{ old('identifier', $query ?? '') }}" required>
                  @error('identifier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-2">
                  <div class="col-7">
                    <label class="form-label">Filter Bulan (opsional)</label>
                    <input type="month" name="month" class="form-control" value="{{ old('month', $month ?? '') }}">
                  </div>
                  <div class="col-5 d-flex align-items-end">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="only_unpaid" value="1"
                             id="onlyUnpaid" {{ old('only_unpaid', $onlyUnpaid ?? false) ? 'checked' : '' }}>
                      <label class="form-check-label" for="onlyUnpaid">
                        Hanya yang belum bayar
                      </label>
                    </div>
                  </div>
                </div>

                <div class="d-grid mt-3">
                  <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search me-1"></i> Tampilkan Tagihan
                  </button>
                </div>

                <div class="text-secondary small mt-3">
                  Data pembayaran dienkripsi dan aman. Jika mengalami kendala, hubungi tim kami.
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="card card-lite shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center">
                  <div class="icon-circle me-2"><i class="bi bi-list-check"></i></div>
                  <div>
                    <div class="fw-semibold">Hasil Tagihan</div>
                    <small class="text-secondary">
                      @if(!empty($query))
                        Menampilkan invoice untuk: <strong>{{ $query }}</strong>
                      @else
                        Tagihan Anda akan tampil di sini setelah pencarian.
                      @endif
                    </small>
                  </div>
                </div>
                @if(!empty($invoices) && $invoices->count())
                  <span class="badge bg-primary">{{ $invoices->count() }} invoice</span>
                @endif
              </div>

              @if(!empty($invoices) && $invoices->count())
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Invoice</th>
                        <th>Periode</th>
                        <th>Plan</th>
                        <th class="text-end">Jumlah</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($invoices as $inv)
                        @php
                          $badge = ['unpaid'=>'warning','paid'=>'success','void'=>'secondary'][$inv->status] ?? 'secondary';
                        @endphp
                        <tr>
                          <td>
                            <div class="fw-semibold">{{ $inv->invoice_number }}</div>
                            <small class="text-secondary">{{ \Carbon\Carbon::parse($inv->billing_date)->format('d M Y') }}</small>
                          </td>
                          <td>{{ $inv->billing_month }}</td>
                          <td>
                            {{ $inv->plan_name ?? 'Internet Service' }}
                            @if($inv->bandwidth_mbps)
                              <small class="text-secondary d-block">{{ $inv->bandwidth_mbps }} Mbps</small>
                            @endif
                          </td>
                          <td class="text-end">Rp {{ number_format($inv->price_inc_ppn,0,',','.') }}</td>
                          <td><span class="badge bg-{{ $badge }}">{{ strtoupper($inv->status) }}</span></td>
                          <td class="text-end">
                            <div class="btn-group">
                              <a href="{{ route('invoices.pdf', $inv) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="bi bi-file-earmark-text"></i> PDF
                              </a>
                              @if($inv->status === 'unpaid')
                                <a href="{{ route('pay.invoice', $inv) }}" class="btn btn-sm btn-primary">
                                  <i class="bi bi-credit-card"></i> Bayar Sekarang
                                </a>
                              @else
                                <span class="btn btn-sm btn-outline-success disabled">
                                  <i class="bi bi-check-circle"></i> Lunas
                                </span>
                              @endif
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <div class="text-secondary">
                  <i class="bi bi-info-circle me-1"></i> Belum ada data yang ditampilkan. Silakan gunakan formulir di samping untuk mencari tagihan Anda.
                </div>
              @endif

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- VOUCHER HOTSPOT --}}
  <section id="voucher" class="py-5 bg-light">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-6">
          <span class="badge badge-soft mb-2"><i class="bi bi-ticket-perforated me-1"></i> Voucher Hotspot</span>
          <h3 class="fw-semibold mb-2">Beli Voucher Hotspot</h3>
          <p class="text-secondary mb-3">
            Top-up akses internet instan untuk perangkat Anda. Pilih durasi & kuota, bayar, dan konek!
          </p>
          <ul class="list-unstyled text-secondary mb-4">
            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> Paket fleksibel harian & mingguan</li>
            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> Pembayaran cepat & aman</li>
            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> Kode voucher langsung tampil & dikirim</li>
          </ul>
          {{-- tombol ini juga buka modal --}}
          <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#buyVoucherModal">
            <i class="bi bi-bag-plus me-1"></i> Beli Sekarang
          </button>
        </div>
        <div class="col-lg-6">
          <div class="glass p-4 shadow-soft">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTbosnjRwq4e8bb7292CTSIEDlNWtU-F8PCgWOO50Cqllgff2gxUfb78GKxLujY4ctBwUU&usqp=CAU" class="img-fluid" alt="Voucher Illustration">
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="py-4">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="text-secondary small">
        © {{ date('Y') }} {{ config('app.name', 'CRM WiFi') }}. All rights reserved.
      </div>
      <div class="text-secondary small">
        <i class="bi bi-telephone me-1"></i> +62-21-123456 • <i class="bi bi-envelope ms-2 me-1"></i> billing@example.com
      </div>
    </div>
  </footer>

  {{-- ===== MODAL BELI VOUCHER ===== --}}
  @php
    // fallback supaya nggak error jika controller belum mengirim $products
    $products = ($products ?? collect());
  @endphp
  <div class="modal fade" id="buyVoucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Beli Voucher Hotspot</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @if(session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
          @endif

          <div class="row g-3">
            <div class="col-lg-5">
              <div class="mb-2 fw-semibold">Pilih Paket</div>
              <div class="list-group" id="productList" style="max-height: 360px; overflow:auto;">
                @forelse($products as $p)
                  @php
                    $label = trim(($p->profile ? $p->profile.' • ' : '')
                           .($p->duration_minutes ? $p->duration_minutes.' min' : 'No limit')
                           .' • '.($p->quota_mb ? number_format($p->quota_mb).' MB' : 'Unlimited'));
                  @endphp
                  <button type="button" class="list-group-item list-group-item-action product-item"
                          data-profile="{{ $p->profile }}"
                          data-price="{{ (int)($p->price ?? 0) }}"
                          data-label="{{ $label }}">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <div class="fw-semibold">{{ $label }}</div>
                        <small class="text-secondary">Tersedia</small>
                      </div>
                      <div class="fw-bold">Rp {{ number_format($p->price ?? 0,0,',','.') }}</div>
                    </div>
                  </button>
                @empty
                  <div class="text-secondary">Belum ada voucher tersedia. Silakan kembali lagi.</div>
                @endforelse
              </div>
              <small class="text-secondary d-block mt-2">Paket berdasarkan stok voucher yang tersedia.</small>
            </div>

            <div class="col-lg-7">
              <form method="POST" action="{{ route('public.voucher.checkout') }}" id="voucherForm">
                @csrf
                <div class="mb-2 fw-semibold">Detail Pembelian</div>
                <div class="row g-2">
                  <div class="col-12">
                    <label class="form-label">Paket Terpilih</label>
                    <input class="form-control" id="selectedLabel" readonly placeholder="Pilih paket di kiri">
                  </div>
                  <input type="hidden" name="product_profile" id="product_profile">
                  <input type="hidden" name="product_price" id="product_price">

                  <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input name="buyer_name" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input name="buyer_phone" class="form-control" required placeholder="08xxxxxxxxxx">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Email (opsional)</label>
                    <input type="email" name="buyer_email" class="form-control" placeholder="Jika ingin voucher dikirim via email">
                  </div>
                  <div class="col-12">
                    <div class="alert alert-light border small mb-0">
                      Setelah memilih paket dan mengisi data, klik <strong>Pesan Sekarang</strong>.
                      Kami akan kirim link pembayaran / instruksi aktivasi voucher.
                    </div>
                  </div>
                </div>
                <div class="d-grid mt-3">
                  <button class="btn btn-primary" type="submit" id="btnCheckout" disabled>Pesan Sekarang</button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  {{-- ===== END MODAL ===== --}}

  {{-- Bootstrap JS Bundle (wajib untuk modal) --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Bootstrap validation
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) { event.preventDefault(); event.stopPropagation(); }
          form.classList.add('was-validated');
        }, false);
      });
    })();

    // Pilih paket → isi hidden field & enable tombol
    document.querySelectorAll('.product-item').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.product-item').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const label = btn.dataset.label || '';
        const profile = btn.dataset.profile || '';
        const price = btn.dataset.price || 0;
        document.getElementById('selectedLabel').value = label;
        document.getElementById('product_profile').value = profile;
        document.getElementById('product_price').value = price;
        document.getElementById('btnCheckout').disabled = false;
      });
    });
  </script>
</body>
</html>
