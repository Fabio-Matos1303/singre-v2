<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('legacy_number')->nullable()->index()->after('id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('legacy_code')->nullable()->index()->after('sku');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->string('legacy_code')->nullable()->index()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('legacy_number');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('legacy_code');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('legacy_code');
        });
    }
};
