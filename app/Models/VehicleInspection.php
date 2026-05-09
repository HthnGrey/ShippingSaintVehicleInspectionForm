<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleInspection extends Model
{
    protected $fillable = [
        'vehicle_id',
        'inspection_type',
        'driver_name',
        'starting_mileage',
        'ending_mileage',
        'fuel_level',
        'tires_ok',
        'lights_ok',
        'brakes_ok',
        'fluids_ok',
        'damage_found',
        'damage_notes',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
