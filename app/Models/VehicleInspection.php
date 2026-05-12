<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VehicleInspection extends Model
{
    protected $fillable = [
        'vehicle_id',
        'pre_trip_inspection_id',
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
        'damage_photo_path',
    ];

    protected $appends = [
        'damage_photo_url',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function preTrip()
    {
        return $this->belongsTo(VehicleInspection::class, 'pre_trip_inspection_id');
    }

    public function pairedPostTrip()
    {
        return $this->hasOne(VehicleInspection::class, 'pre_trip_inspection_id');
    }

    protected function damagePhotoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->damage_photo_path
            ? Storage::disk('public')->url($this->damage_photo_path)
            : null);
    }
}
