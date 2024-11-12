<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\SimData;
use App\Models\SimAssign;
use App\Models\AssignVendor;
use Illuminate\Http\Request;
use App\Models\VendorAssignment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SimAssignController extends Controller
{
    public function simAssignByAdmin(Request $request)
    {
        $request->validate([
            'salesman_id' => 'required',
            'sim_numbers' => 'required|array',
            'sim_numbers.*' => 'required|string|distinct',
        ]);

        $salesman = User::find($request->salesman_id);
        if (!$salesman || $salesman->role !== 'sales') {
            return response()->json(['message' => 'User does not have the sales role'], 403);
        }

        // Get all submitted SIM numbers
        $requestedSims = collect($request->sim_numbers);

        // Get all existing SIMs from database
        $existingSims = SimData::whereIn('sim_number', $requestedSims)->get();

        // Find non-existent SIMs
        $nonExistentSims = $requestedSims->diff($existingSims->pluck('sim_number'));

        // Find already assigned SIMs
        $alreadyAssignedSims = $existingSims->where('is_assigned', true)->pluck('sim_number');

        // Get valid SIMs for assignment
        $validSims = $existingSims->where('is_assigned', null)->pluck('sim_number');


        // Prepare response data
        $responseData = [
            'valid_sims' => $validSims->values()->toArray(),
            'non_existent_sims' => $nonExistentSims->values()->toArray(),
            'already_assigned_sims' => $alreadyAssignedSims->values()->toArray()
        ];

        // If no valid SIMs to assign, return early
        if ($validSims->isEmpty()) {
            return response()->json([
                'message' => 'No valid SIMs to assign',
                'data' => $responseData
            ], 400);
        }

        // Assign valid SIMs
        SimData::whereIn('sim_number', $validSims)->update(['is_assigned' => true]);

        // Create assignment records for valid SIMs
        $assignments = $validSims->map(function ($simNumber) use ($request) {
            return [
                'admin_id' => auth()->id(),
                'salesman_id' => $request->salesman_id,
                'sim_numbers' => $simNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        SimAssign::insert($assignments);

        return response()->json([
            'message' => 'SIM cards assigned to salesman successfully',
            'data' => $responseData
        ], 200);
    }




    public function simAssignBySalesman(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
            'sim_numbers' => 'required|array',
            'sim_numbers.*' => 'required|string|distinct',
        ]);

        $vendor = User::find($request->vendor_id);
        if (!$vendor || $vendor->role !== 'vendor') {
            return response()->json(['message' => 'User does not have the vendor role'], 403);
        }

        $requestedSims = collect($request->sim_numbers);

        // Check if SIMs are assigned to the current salesman
        $salesmanSims = SimAssign::whereIn('sim_numbers', $requestedSims)
            ->where('salesman_id', auth()->id())
            ->get();

        // Get the exact sim numbers from salesman's assignments
        $salesmanSimNumbers = $salesmanSims->pluck('sim_numbers')->toArray();

        // Find sims that are not owned by the salesman
        $notOwnedSims = $requestedSims->filter(function ($sim) use ($salesmanSimNumbers) {
            return !in_array($sim, $salesmanSimNumbers);
        })->values();

        $alreadyAssignedToVendor = $salesmanSims->where('status', true)->pluck('sim_numbers');

        $validSims = $salesmanSims->where('status', null)->pluck('sim_numbers');

        $responseData = [
            'valid_sims' => $validSims->values()->toArray(),
            'not_owned_sims' => $notOwnedSims->values()->toArray(),
            'already_assigned_sims' => $alreadyAssignedToVendor->values()->toArray()
        ];

        if ($validSims->isEmpty()) {
            return response()->json([
                'message' => 'No valid SIMs to assign',
                'data' => $responseData
            ], 400);
        }

        SimAssign::whereIn('sim_numbers', $validSims)->update(['status' => true]);

        $assignments = $validSims->map(function ($simNumber) use ($request) {
            return [
                'salesman_id' => auth()->id(),
                'vendor_id' => $request->vendor_id,
                'sim_numbers' => $simNumber,
                'created_at' => now(),
                'updated_at' => now(),

            ];
        })->toArray();

        AssignVendor::insert($assignments);

        return response()->json([
            'message' => 'SIM cards assigned to vendor successfully',
            'data' => $responseData
        ], 200);
    }

    public function getSimAssignmentsForAdmin()
    {
        $assignedSims = SimAssign::select('salesman_id')
            ->selectRaw('COUNT(*) as total_sims')
            ->selectRaw('SUM(CASE WHEN status = true THEN 1 ELSE 0 END) as assigned_to_vendor')
            ->selectRaw('SUM(CASE WHEN  status = 0 OR status IS NULL THEN 1 ELSE 0 END) as available_sims')
            ->groupBy('salesman_id')
            ->get()
            ->map(function ($assignment) {
                $salesman = User::find($assignment->salesman_id);

                $vendorAssignments = AssignVendor::where('salesman_id', $assignment->salesman_id)->get()
                    ->map(function ($vendorAssign) {
                        $vendor = User::find($vendorAssign->vendor_id);
                        $totalSimsForVendor = AssignVendor::where('vendor_id', $vendorAssign->vendor_id)
                            ->count();  // Counts the number of SIM assignments for the vendor
        
                        return [
                            'vendor' => $vendor ? $vendor->name : null,
                            'sim_number' => $vendorAssign->sim_numbers,
                            'assigned_at' => $vendorAssign->created_at,
                            'total_sims_assigned_to_vendor' => $totalSimsForVendor
                        ];
                    });


                return [
                    'salesman' => $salesman ? $salesman->name : null,
                    'total_sims' => $assignment->total_sims,
                    'assigned_to_vendor' => $assignment->assigned_to_vendor,
                    'available_sims' => $assignment->available_sims,
                    'sims_detail' => $assignment->sim_numbers,
                    'assigned_at' => $assignment->created_at,
                    'vendor_assignments' => $vendorAssignments,

                ];
            });

        return response()->json([
            'status' => 'success',
            'total_sims_assigned' => $assignedSims->sum('total_sims'),
            'total_vendor_assigned' => $assignedSims->sum('assigned_to_vendor'),
            'total_available' => $assignedSims->sum('available_sims'),
            'assignments' => $assignedSims
        ], 200);
    }


    public function getSimAssignmentsForSalesman()
    {
        $salesmanId = auth()->id();

        $assignedSims = SimAssign::where('salesman_id', $salesmanId)
            ->select('salesman_id')
            ->selectRaw('COUNT(*) as total_sims')
            ->selectRaw('SUM(CASE WHEN status = true THEN 1 ELSE 0 END) as assigned_to_vendor')
            ->selectRaw('SUM(CASE WHEN status = false OR status IS NULL THEN 1 ELSE 0 END) as available_sims')
            ->groupBy('salesman_id')
            ->first();

        $vendorAssignments = AssignVendor::where('salesman_id', $salesmanId)
            ->get()
            ->groupBy('vendor_id')
            ->map(function ($assignments) {
                $vendor = User::find($assignments->first()->vendor_id);

                return [
                    'vendor_name' => $vendor ? $vendor->name : null,
                    'total_sims' => $assignments->count(),
                    'sim_numbers' => $assignments->pluck('sim_numbers'),
                    'assigned_at' => $assignments->first()->created_at->format('d-m-y'),
                ];
            })->values();

        // Get all SIM details for this salesman and format `created_at`
        $simDetails = SimAssign::where('salesman_id', $salesmanId)
            ->get(['sim_numbers', 'created_at', 'status'])
            ->map(function ($sim) {
                return [
                    'sim_number' => $sim->sim_numbers,
                    'created_at' => $sim->created_at->format('d-m-y'),
                    'status' => $sim->status
                ];
            });

        return response()->json([
            'status' => 'success',
            'summary' => [
                'total_sims' => $assignedSims->total_sims ?? 0,
                'assigned_to_vendor' => $assignedSims->assigned_to_vendor ?? 0,
                'available_sims' => $assignedSims->available_sims ?? 0,
            ],
            'vendor_assignments' => $vendorAssignments,
            'sims_detail' => $simDetails
        ], 200);
    }


    //salesman can view sims assigned to him by admin
    public function viewSalesmanSims()
    {
        $salesman = Auth::user();

        if ($salesman->role !== 'sales') {
            return response()->json(['message' => 'User does not have the sales role'], 403);
        }

        $assignments = SimAssign::where('salesman_id', $salesman->id)->get(['sim_numbers', 'status']);

        return response()->json([
            'status' => 'success',
            'data' => $assignments
        ], 200);
    }

    //vendor can view sims assigned to him by which salesman
    public function viewVendorSims()
    {
        $vendor = Auth::user();

        if ($vendor->role !== 'vendor') {
            return response()->json(['message' => 'User does not have the vendor role'], 403);
        }
        
        $assignments = AssignVendor::where('vendor_id', $vendor->id)
        ->get(['sim_numbers', 'salesman_id'])
        ->map(function ($assignment) {
            $salesman = User::find($assignment->salesman_id);
            return [
                'sim_number' => $assignment->sim_numbers,
                'salesman_name' => $salesman ? $salesman->name : null, 
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $assignments
        ], 200);
    }








}

