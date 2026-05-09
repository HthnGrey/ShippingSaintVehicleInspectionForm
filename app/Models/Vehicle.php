<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'name',
        'plate_number',
        'current_mileage',
        'status',
        'notes',
    ];

    public function inspections()
    {
        return $this->hasMany(VehicleInspection::class);
    }
}
