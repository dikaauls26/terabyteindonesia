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
        Schema::create('invoice_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained();
            $t->string('description');
            $t->unsignedInteger('qty')->default(1);
            $t->decimal('unit_price', 12, 2);
            $t->decimal('line_total', 12, 2);
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
