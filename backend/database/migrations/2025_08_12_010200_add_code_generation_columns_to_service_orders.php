<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->unsignedInteger('sequence_year')->nullable()->after('code');
            $table->unsignedInteger('sequence_number')->nullable()->after('sequence_year');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn(['sequence_year','sequence_number']);
        });
    }
};
