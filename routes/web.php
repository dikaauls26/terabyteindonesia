<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\{
  HomeController,
  SiteController,
  PlanController,
  CustomerController,
  InvoiceController,
  InvoiceSendController,
  PaymentController,
  PaymentWebhookController,
  PublicController,
  HotspotVoucherController,
};

/*
|--------------------------------------------------------------------------
| Public Routes (tanpa login)
|--------------------------------------------------------------------------
*/

// Landing publik
Route::get('/', [PublicController::class, 'welcome'])->name('welcome');

// Cek tagihan publik (lookup by email / customer no)
Route::post('/check-bills', [PublicController::class, 'checkBills'])->name('public.check-bills');

// Checkout voucher (stub – nanti integrasi pembayaran)
Route::post('/voucher/checkout', [PublicController::class, 'voucherCheckout'])->name('public.voucher.checkout');

// Bayar invoice (publik) – supaya tombol "Bayar Sekarang" di welcome tidak minta login
Route::get('pay/{invoice}', [PaymentController::class, 'redirectToSnap'])
  ->whereNumber('invoice')
  ->name('pay.invoice');

// PDF invoice (publik) – tombol PDF di welcome tidak minta login
Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
  ->whereNumber('invoice')
  ->name('invoices.pdf');

/*
|--------------------------------------------------------------------------
| Auth Scaffolding
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Internal Routes (wajib login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

  // Dashboard internal
  Route::get('/home', [HomeController::class, 'index'])->name('home');

  /*
  |-----------------------------
  | Customers Map (HARUS di atas resource)
  |-----------------------------
  */
  Route::get('customers/map', [CustomerController::class, 'map'])->name('customers.map');
  Route::get('customers/map-data', [CustomerController::class, 'mapData'])->name('customers.map-data');

  /*
  |-----------------------------
  | Master Data
  |-----------------------------
  | Sites & Plans pakai modal di index (create/edit/show tidak dipakai)
  */
  Route::resource('sites', SiteController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['sites' => 'site'])
    ->missing(fn() => abort(404));

  Route::resource('plans', PlanController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['plans' => 'plan'])
    ->missing(fn() => abort(404));

  // Vouchers (admin)
  Route::resource('vouchers', HotspotVoucherController::class)->except(['show']);

  /*
  |-----------------------------
  | Customers
  |-----------------------------
  | Exclude 'show' agar /customers/map tidak dibaca sbg {customer}
  | Kunci parameter numeric supaya string (seperti 'map') tidak tertangkap.
  */
  Route::resource('customers', CustomerController::class)
    ->except(['show'])
    ->parameters(['customers' => 'customer'])
    ->where(['customer' => '[0-9]+'])
    ->missing(fn() => abort(404));

  /*
  |-----------------------------
  | Invoices (internal)
  |-----------------------------
  | Index/generate/kirim/hapus hanya untuk staff.
  */
  Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');

  Route::post('invoices/generate-all', [InvoiceController::class, 'generateAll'])
    ->name('invoices.generate-all');

  Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])
    ->whereNumber('invoice')->name('invoices.show');

  Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])
    ->whereNumber('invoice')->name('invoices.destroy');

  // Kirim (stub tombol)
  Route::post('invoices/{invoice}/send-whatsapp', [InvoiceSendController::class, 'whatsapp'])
    ->whereNumber('invoice')->name('invoices.send.whatsapp');

  Route::post('invoices/{invoice}/send-email', [InvoiceSendController::class, 'email'])
    ->whereNumber('invoice')->name('invoices.send.email');

  Route::post('invoices/blast-email', [InvoiceSendController::class, 'blastEmail'])
    ->name('invoices.blast.email');

  Route::post('invoices/blast-whatsapp', [InvoiceSendController::class, 'blastWhatsapp'])
    ->name('invoices.blast.whatsapp');
});

/*
|--------------------------------------------------------------------------
| Webhook Midtrans (public)
|--------------------------------------------------------------------------
*/
Route::post('midtrans/webhook', [PaymentWebhookController::class, 'handle'])
  ->name('midtrans.webhook');
