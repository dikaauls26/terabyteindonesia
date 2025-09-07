<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            if (!Schema::hasColumn('customers', 'ont_brand')) {
                $t->string('ont_brand')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('customers', 'ont_sn')) {
                $t->string('ont_sn')->nullable()->after('ont_brand');
            }
            if (!Schema::hasColumn('customers', 'latitude')) {
                $t->decimal('latitude', 10, 7)->nullable()->after('ont_sn');
            }
            if (!Schema::hasColumn('customers', 'longitude')) {
                $t->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            if (Schema::hasColumn('customers', 'longitude')) $t->dropColumn('longitude');
            if (Schema::hasColumn('customers', 'latitude'))  $t->dropColumn('latitude');
            if (Schema::hasColumn('customers', 'ont_sn'))   $t->dropColumn('ont_sn');
            if (Schema::hasColumn('customers', 'ont_brand')) $t->dropColumn('ont_brand');
        });
    }
};
