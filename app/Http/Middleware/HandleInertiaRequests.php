<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $request->user() ? [
                    'viewDashboard' => Gate::allows('view-dashboard'),
                    'manageUsers' => Gate::allows('manage-users'),
                    'viewVehicles' => Gate::allows('view-vehicles'),
                    'createVehicles' => Gate::allows('create-vehicles'),
                    'updateVehicles' => Gate::allows('update-vehicles'),
                    'deleteVehicles' => Gate::allows('delete-vehicles'),
                    'manageWorkOrders' => Gate::allows('manage-work-orders'),
                    'viewDriverMileage' => Gate::allows('view-driver-mileage'),
                    'viewDriverHistory' => Gate::allows('view-driver-history'),
                    'viewAuditLogs' => Gate::allows('view-audit-logs'),
                    'viewInspections' => Gate::allows('view-inspections'),
                    'createInspections' => Gate::allows('create-inspections'),
                    'manageInspections' => Gate::allows('manage-inspections'),
                    'viewReports' => Gate::allows('view-reports'),
                    'manageReports' => Gate::allows('manage-reports'),
                ] : [],
            ],
        ];
    }
}
