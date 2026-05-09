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
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('inspection_type');
            $table->string('driver_name');
            $table->integer('starting_mileage');
            $table->integer('ending_mileage')->nullable();
            $table->string('fuel_level')->nullable();

            $table->boolean('tires_ok')->default(false);
            $table->boolean('lights_ok')->default(false);
            $table->boolean('brakes_ok')->default(false);
            $table->boolean('fluids_ok')->default(false);
            $table->boolean('damage_found')->default(false);

            $table->text('damage_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};
