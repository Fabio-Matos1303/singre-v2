<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
            $table->index(['service_order_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_status_histories');
    }
};
