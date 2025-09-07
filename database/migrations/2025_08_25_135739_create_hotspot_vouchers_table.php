// database/migrations/2025_08_25_000000_create_hotspot_vouchers_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('hotspot_vouchers', function (Blueprint $t) {
      $t->id();
      $t->string('code')->unique();                 // Kode dari Mikrotik
      $t->string('profile')->nullable();            // nama profile Mikrotik / paket
      $t->integer('duration_minutes')->nullable();  // durasi pakai (menit)
      $t->integer('quota_mb')->nullable();          // kuota (MB) optional
      $t->integer('price')->default(0);             // harga jual
      $t->string('currency', 10)->default('IDR');

      // status lifecycle
      // available | reserved | sold | redeemed | expired | disabled
      $t->string('status', 20)->default('available');

      // metadata opsional
      $t->string('batch_id')->nullable();           // identifikasi batch import
      $t->string('router')->nullable();             // nama router mikrotik

      // buyer info (diisi saat proses pembelian)
      $t->string('buyer_name')->nullable();
      $t->string('buyer_email')->nullable();
      $t->string('buyer_phone')->nullable();
      $t->timestamp('reserved_at')->nullable();
      $t->timestamp('sold_at')->nullable();
      $t->timestamp('redeemed_at')->nullable();
      $t->timestamp('expired_at')->nullable();

      $t->boolean('is_active')->default(true);      // hard toggle
      $t->text('notes')->nullable();

      $t->timestamps();
      $t->index(['status','is_active']);
      $t->index(['profile','price']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('hotspot_vouchers');
  }
};
