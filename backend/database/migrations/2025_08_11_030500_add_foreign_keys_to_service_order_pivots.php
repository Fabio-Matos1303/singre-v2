<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_product', function (Blueprint $table) {
            if (!Schema::hasColumn('service_order_product','service_order_id')) return;
            $table->index('service_order_id');
            $table->index('product_id');
            $table->foreign('service_order_id')->references('id')->on('service_orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
        Schema::table('service_order_service', function (Blueprint $table) {
            if (!Schema::hasColumn('service_order_service','service_order_id')) return;
            $table->index('service_order_id');
            $table->index('service_id');
            $table->foreign('service_order_id')->references('id')->on('service_orders')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_order_product', function (Blueprint $table) {
            $table->dropForeign(['service_order_id']);
            $table->dropForeign(['product_id']);
            $table->dropIndex(['service_order_id']);
            $table->dropIndex(['product_id']);
        });
        Schema::table('service_order_service', function (Blueprint $table) {
            $table->dropForeign(['service_order_id']);
            $table->dropForeign(['service_id']);
            $table->dropIndex(['service_order_id']);
            $table->dropIndex(['service_id']);
        });
    }
};
