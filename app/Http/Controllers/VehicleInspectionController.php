<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class VehicleInspectionController extends Controller
{
    public function index()
    {
        return Inertia::render('Inspections/Index', [
            'inspections' => VehicleInspection::with(['vehicle', 'preTrip.vehicle', 'pairedPostTrip'])
                ->latest()
                ->get(),
        ]);
    }

    public function createPreTrip()
    {
        return Inertia::render('Inspections/PreTrip', [
            'vehicles' => Vehicle::orderBy('name')->get(),
        ]);
    }

    public function storePreTrip(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'starting_mileage' => ['required', 'integer', 'min:0'],
            'fuel_level' => ['nullable', 'string', 'max:255'],
            'tires_ok' => ['required', 'boolean'],
            'lights_ok' => ['required', 'boolean'],
            'brakes_ok' => ['required', 'boolean'],
            'fluids_ok' => ['required', 'boolean'],
            'damage_found' => ['required', 'boolean'],
            'damage_notes' => ['nullable', 'string'],
        ]);

        $vehicle = $this->vehicleForInspection($data['vehicle_id'], 'Pre Trip');

        $data['inspection_type'] = 'Pre Trip';
        $data['driver_name'] = $request->user()->name;

        $inspection = VehicleInspection::create($data);

        AuditLog::record('created', $inspection, "Created pre trip inspection for {$vehicle->name}");

        $vehicle->update([
            'status' => $data['damage_found'] ? 'Needs Maintenance' : 'In Use',
        ]);

        return redirect()->route('dashboard');
    }

    public function createPostTrip()
    {
        return Inertia::render('Inspections/PostTrip', [
            'vehicles' => Vehicle::orderBy('name')->get(),
            'preTripInspections' => VehicleInspection::where('inspection_type', 'Pre Trip')
                ->whereDoesntHave('pairedPostTrip')
                ->latest()
                ->get(),
        ]);
    }

    public function storePostTrip(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'starting_mileage' => ['required', 'integer', 'min:0'],
            'ending_mileage' => ['required', 'integer', 'gte:starting_mileage'],
            'fuel_level' => ['nullable', 'string', 'max:255'],
            'tires_ok' => ['required', 'boolean'],
            'lights_ok' => ['required', 'boolean'],
            'brakes_ok' => ['required', 'boolean'],
            'fluids_ok' => ['required', 'boolean'],
            'damage_found' => ['required', 'boolean'],
            'damage_notes' => ['required_if:damage_found,true', 'nullable', 'string'],
            'damage_photo' => [
                Rule::requiredIf(fn () => $request->boolean('damage_found')),
                'nullable',
                'image',
                'max:5120',
            ],
        ]);

        $vehicle = $this->vehicleForInspection($data['vehicle_id'], 'Post Trip');
        $preTrip = $this->preTripForPostTrip($vehicle);

        $data['inspection_type'] = 'Post Trip';
        $data['pre_trip_inspection_id'] = $preTrip->id;
        $data['driver_name'] = $request->user()->name;

        if ($request->hasFile('damage_photo')) {
            $data['damage_photo_path'] = $request->file('damage_photo')->store('damage-photos', 'public');
        }

        unset($data['damage_photo']);

        $inspection = VehicleInspection::create($data);

        AuditLog::record('created', $inspection, "Created post trip inspection for {$vehicle->name}", [
            'damage_found' => (bool) $inspection->damage_found,
            'damage_photo_path' => $inspection->damage_photo_path,
        ]);

        $vehicle->update([
            'current_mileage' => $data['ending_mileage'],
            'status' => $this->statusAfterPostTrip($vehicle, $data),
        ]);

        return redirect()->route('dashboard');
    }

    public function edit(VehicleInspection $inspection)
    {
        $inspection->load(['vehicle', 'preTrip.vehicle']);

        return Inertia::render('Inspections/Edit', [
            'inspection' => $inspection,
        ]);
    }

    public function update(Request $request, VehicleInspection $inspection): RedirectResponse
    {
        $data = $request->validate($this->rulesFor($inspection));

        if ($inspection->inspection_type === 'Post Trip') {
            $data['ending_mileage'] = (int) $data['ending_mileage'];
        }

        $inspection->update($data);

        AuditLog::record('updated', $inspection, "Updated {$inspection->inspection_type} inspection", [
            'inspection_id' => $inspection->id,
        ]);

        $this->syncVehicleAfterInspectionChange($inspection->fresh('vehicle'));

        return redirect()->route('inspections.index');
    }

    public function destroy(VehicleInspection $inspection): RedirectResponse
    {
        $vehicle = $inspection->vehicle;

        $inspection->delete();

        AuditLog::record('deleted', $inspection, "Deleted {$inspection->inspection_type} inspection", [
            'inspection_id' => $inspection->id,
            'vehicle_id' => $vehicle?->id,
        ]);

        if ($vehicle) {
            $this->syncVehicleAfterInspectionDelete($vehicle);
        }

        return redirect()->route('inspections.index');
    }

    private function vehicleForInspection(int|string $vehicleId, string $inspectionType): Vehicle
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        if ($inspectionType === 'Pre Trip' && $vehicle->status === 'In Use') {
            throw ValidationException::withMessages([
                'vehicle_id' => 'You cannot create a pre trip inspection for a vehicle that is in use.',
            ]);
        }

        if ($inspectionType === 'Pre Trip' && in_array($vehicle->status, ['Maintenance Required', 'Needs Maintenance'], true)) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Maintenance must be completed before this vehicle can be used.',
            ]);
        }

        if ($inspectionType === 'Post Trip' && $vehicle->status === 'Available') {
            throw ValidationException::withMessages([
                'vehicle_id' => 'You cannot create a post trip inspection for a vehicle that is available.',
            ]);
        }

        return $vehicle;
    }

    private function statusAfterPostTrip(Vehicle $vehicle, array $data): string
    {
        if ($this->postTripNeedsMaintenance($data)) {
            return 'Maintenance Required';
        }

        if ($data['ending_mileage'] >= $vehicle->required_maintenance_mileage) {
            return 'Maintenance Required';
        }

        return 'Available';
    }

    private function postTripNeedsMaintenance(array $data): bool
    {
        return $data['damage_found']
            || ! $data['tires_ok']
            || ! $data['lights_ok']
            || ! $data['brakes_ok']
            || ! $data['fluids_ok'];
    }

    private function preTripForPostTrip(Vehicle $vehicle): VehicleInspection
    {
        $preTrip = VehicleInspection::where('vehicle_id', $vehicle->id)
            ->where('inspection_type', 'Pre Trip')
            ->whereDoesntHave('pairedPostTrip')
            ->latest()
            ->first();

        if (! $preTrip) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'No open pre trip inspection was found for this vehicle.',
            ]);
        }

        return $preTrip;
    }

    private function rulesFor(VehicleInspection $inspection): array
    {
        $rules = [
            'driver_name' => ['required', 'string', 'max:255'],
            'starting_mileage' => ['required', 'integer', 'min:0'],
            'fuel_level' => ['nullable', 'string', 'max:255'],
            'tires_ok' => ['required', 'boolean'],
            'lights_ok' => ['required', 'boolean'],
            'brakes_ok' => ['required', 'boolean'],
            'fluids_ok' => ['required', 'boolean'],
            'damage_found' => ['required', 'boolean'],
        ];

        if ($inspection->inspection_type === 'Post Trip') {
            $rules['ending_mileage'] = ['required', 'integer', 'gte:starting_mileage'];
            $rules['damage_notes'] = ['required_if:damage_found,true', 'nullable', 'string'];
        } else {
            $rules['damage_notes'] = ['nullable', 'string'];
        }

        return $rules;
    }

    private function syncVehicleAfterInspectionChange(VehicleInspection $inspection): void
    {
        $vehicle = $inspection->vehicle;

        if (! $vehicle) {
            return;
        }

        if ($inspection->inspection_type === 'Post Trip') {
            $vehicle->update([
                'current_mileage' => $inspection->ending_mileage,
                'status' => $this->statusAfterPostTrip($vehicle, $inspection->toArray()),
            ]);

            return;
        }

        if (! $inspection->pairedPostTrip()->exists()) {
            $vehicle->update([
                'status' => $inspection->damage_found ? 'Needs Maintenance' : 'In Use',
            ]);
        }
    }

    private function syncVehicleAfterInspectionDelete(Vehicle $vehicle): void
    {
        $openPreTrip = $vehicle->inspections()
            ->where('inspection_type', 'Pre Trip')
            ->whereDoesntHave('pairedPostTrip')
            ->latest()
            ->first();

        if ($openPreTrip) {
            $vehicle->update([
                'status' => $openPreTrip->damage_found ? 'Needs Maintenance' : 'In Use',
            ]);

            return;
        }

        $latestPostTrip = $vehicle->inspections()
            ->where('inspection_type', 'Post Trip')
            ->latest()
            ->first();

        if ($latestPostTrip) {
            $vehicle->update([
                'current_mileage' => $latestPostTrip->ending_mileage,
                'status' => $this->statusAfterPostTrip($vehicle, $latestPostTrip->toArray()),
            ]);

            return;
        }

        $vehicle->update(['status' => 'Available']);
    }
}
