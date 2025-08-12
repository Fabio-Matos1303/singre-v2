<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('serial_company')->nullable()->unique();
            $table->string('serial_manufacturer')->nullable()->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->text('configuration')->nullable();
            $table->unsignedInteger('intervention_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
