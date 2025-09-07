<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('customers', function (Blueprint $t) {
            if (!Schema::hasColumn('customers','installed_at')) {
                $t->date('installed_at')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('customers','technician_name')) {
                $t->string('technician_name')->nullable()->after('installed_at');
            }
            if (!Schema::hasColumn('customers','service_status')) {
                // active | suspend | terminated
                $t->string('service_status', 20)->default('active')->after('technician_name');
            }
            // index untuk query geospasial sederhana
            if (!Schema::hasColumn('customers','latitude')) {
                // jika sebelumnya belum create kolom lat/lng, abaikan bagian ini (sudah ditangani migration sebelumnya)
            } else {
                $t->index(['latitude','longitude']);
            }
        });
    }

    public function down(): void {
        Schema::table('customers', function (Blueprint $t) {
            if (Schema::hasColumn('customers','service_status')) $t->dropColumn('service_status');
            if (Schema::hasColumn('customers','technician_name')) $t->dropColumn('technician_name');
            if (Schema::hasColumn('customers','installed_at')) $t->dropColumn('installed_at');
            // index lat/lng biarkan saja kalau sudah ada; optional untuk di-drop
        });
    }
};
