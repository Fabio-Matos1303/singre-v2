<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('code')->nullable()->index()->after('legacy_number');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
