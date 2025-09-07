<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_no')->unique();
            $t->foreignId('customer_id')->constrained();
            $t->foreignId('site_id')->constrained();
            $t->json('plan_snapshot_json');
            $t->date('period');
            $t->date('due_date');
            $t->decimal('subtotal', 12, 2);
            $t->decimal('ppn', 12, 2)->default(0);
            $t->decimal('total', 12, 2);
            $t->enum('status', ['unpaid', 'paid', 'expired', 'canceled'])->default('unpaid');
            $t->string('snap_token')->nullable();
            $t->string('snap_redirect_url')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
