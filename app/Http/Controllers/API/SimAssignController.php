<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssignVendor;
use App\Models\SimAssign;
use App\Models\SimData;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SimAssignController extends Controller
{
    public function simAssignByAdmin(Request $request)
    {
        
        
        // Validate the request
        $request->validate([
            'user_id' => 'required',
            'sim_numbers' => 'required|array',
            'sim_numbers.*' => 'required|string|distinct',
        ]);
        
        $user = User::find($request->user_id);

        if (!$user || $user->role !== 'sales') {
            return response()->json(['message' => 'User does not have the sales role'], 403);
        }
        

        // Check if SIM numbers are valid and not assigned
        $invalidSims = SimData::whereIn('sim_number', $request->sim_numbers)
            ->where('is_assigned', true)
            ->pluck('sim_number')
            ->toArray();

        if (!empty($invalidSims)) {
            return response()->json(['message' => 'Some SIM cards are already assigned', 'invalid_sims' => $invalidSims], 400);
        }

        // Update SIM cards as assigned
        SimData::whereIn('sim_number', $request->sim_numbers)->update(['is_assigned' => true]);

        // Create individual assignment records
        $assignments = [];
        foreach ($request->sim_numbers as $simNumber) {
            $assignments[] = [
                'user_id' => $request->user_id,
                'sim_numbers' => $simNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert assignments into the database
        SimAssign::insert($assignments);

        return response()->json(['message' => 'SIM cards assigned successfully'], 200);
    }


    public function simAssignBySalesman(Request $request)
    {
        // Validate the request
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'sim_numbers' => 'required|array',
            'sim_numbers.*' => 'required|string|distinct',
        ]);
        
        // Find the user and check their role
        $user = User::find($request->user_id);

        if (!$user || $user->role !== 'vendor') {
            return response()->json(['message' => 'User does not have the vendor role'], 403);
        }

        // Check if SIM numbers are valid and not assigned
        $invalidSims = SimAssign::whereIn('sim_numbers', $request->sim_numbers)
            ->where('status', true)
            ->pluck('sim_numbers')
            ->toArray();

        if (!empty($invalidSims)) {
            return response()->json(['message' => 'Some SIM cards are already assigned', 'invalid_sims' => $invalidSims], 400);
        }

        // Update SIM cards as assigned
        SimAssign::whereIn('sim_numbers', $request->sim_numbers)->update(['status' => true]);

        // Create individual assignment records
        $assignments = [];
        foreach ($request->sim_numbers as $simNumber) {
            $assignments[] = [
                'user_id' => $request->user_id,
                'sim_numbers' => $simNumber,

            ];
        }

        // Insert assignments into the AssignmentHistory table
        AssignVendor::insert($assignments);

        return response()->json(['message' => 'SIM cards assigned successfully'], 200);
    }


    public function getSimAssignmentsForAdmin()
    {
        // Fetch all SIM assignments
        $data = SimAssign::all();

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    public function getSimAssignmentsForSalesperson()
    {
        // Fetch all vendor assignments
        $data = AssignVendor::all();

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }






}

