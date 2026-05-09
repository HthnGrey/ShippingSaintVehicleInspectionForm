<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->foreignId('pre_trip_inspection_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('vehicle_inspections')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pre_trip_inspection_id');
        });
    }
};
