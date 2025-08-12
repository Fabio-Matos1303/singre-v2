<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_order_types')) {
            Schema::create('service_order_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('service_order_forms')) {
            Schema::create('service_order_forms', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('is_default')->default(false);
                $table->boolean('is_warranty')->default(false);
                $table->boolean('generates_commission')->default(false);
                $table->boolean('require_equipment')->default(false);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('service_order_phases')) {
            Schema::create('service_order_phases', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('is_default')->default(false);
                $table->integer('points')->default(0);
                $table->boolean('generates_commission')->default(false);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('service_order_consultations')) {
            Schema::create('service_order_consultations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        Schema::table('service_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('service_orders', 'type_id')) {
                $table->foreignId('type_id')->nullable()->after('equipment_id')->constrained('service_order_types')->nullOnDelete();
            }
            if (!Schema::hasColumn('service_orders', 'form_id')) {
                $table->foreignId('form_id')->nullable()->after('type_id')->constrained('service_order_forms')->nullOnDelete();
            }
            if (!Schema::hasColumn('service_orders', 'phase_id')) {
                $table->foreignId('phase_id')->nullable()->after('form_id')->constrained('service_order_phases')->nullOnDelete();
            }
            if (!Schema::hasColumn('service_orders', 'consultation_id')) {
                $table->foreignId('consultation_id')->nullable()->after('phase_id')->constrained('service_order_consultations')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('type_id');
            $table->dropConstrainedForeignId('form_id');
            $table->dropConstrainedForeignId('phase_id');
            $table->dropConstrainedForeignId('consultation_id');
        });
        Schema::dropIfExists('service_order_consultations');
        Schema::dropIfExists('service_order_phases');
        Schema::dropIfExists('service_order_forms');
        Schema::dropIfExists('service_order_types');
    }
};
