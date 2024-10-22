<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Route;
use App\Models\Planning;
use App\Models\AssignRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RoutePlanController extends Controller
{

    public function uploadroute(Request $request)
{
    // Validate the request
    $request->validate([
        'file' => 'required|mimes:xlsx,csv|max:2048',
    ]);

    $originalFile = $request->file('file');
    $extension = $originalFile->getClientOriginalExtension();
    $filename = 'routes-' . time() . '.' . $extension; 

    $filePath = $originalFile->storeAs('uploads', $filename);

    $spreadsheet = IOFactory::load(Storage::path($filePath));
    $worksheet = $spreadsheet->getActiveSheet();

    $highestRow = $worksheet->getHighestRow();

    for ($row = 2; $row <= $highestRow; $row++) {
        $postcode = $worksheet->getCell('D' . $row)->getValue(); 
        $area = strtok($postcode, ' '); 
        
        $routeData = new Route();
        $routeData->city = $worksheet->getCell('E' . $row)->getValue();
        $routeData->area = $area;
        $routeData->postcode = $postcode;
        $routeData->shop = $worksheet->getCell('B' . $row)->getValue(); 
        $routeData->address = $worksheet->getCell('C' . $row)->getValue(); 
        $routeData->save();
    }

    return response()->json(['message' => 'File data uploaded successfully']);
}



    public function showroute()
    {
        $data = Route::all();
        return response()->json($data);
    }




    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'date' => 'required',
            'area' => 'required|array', // Ensure area is an array
            'area.*.area' => 'required|string', // Ensure each area has a name
            'area.*.shops' => 'required|array', // Ensure shops is an array
            'area.*.shops.*' => 'integer', // Ensure each shop ID is an integer

        ]);


        // Check if user is a salesperson
        $user = User::find($request->user_id);
        if (!$user || $user->role !== 'sales') {
            return response()->json(['message' => 'User does not exist or doesn\'t have the sales role'], 403);
        }

        // Create an associative array for areas and shops
        $areaShopAssignments = [];
        foreach ($request->area as $areaAssignment) {

            // Check if shops array is empty
            if (empty($areaAssignment['shops'])) {
                return response()->json(['message' => 'Shops array cannot be empty for area: ' . $areaAssignment['area']], 400);
            }

            $areaShopAssignments[] = [
                'area' => $areaAssignment['area'],
                'shops' => $areaAssignment['shops']
            ];
        }

        // Create a new route planning record
        $route = planning::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'area' => json_encode($areaShopAssignments), // Store area-shop assignments as JSON
            'shops' => json_encode(array_merge(...array_column($areaShopAssignments, 'shops'))), // Flatten all shop IDs and store
        ]);

        return response()->json(['message' => 'Shops assigned successfully', 'data' => $route]);
    }

    public function getAllPlannings()
    {

        // Fetch all planning records
        $plannings = Planning::all();

        // Decode the shops JSON string and fetch associated routes
        foreach ($plannings as $planning) {
            $shopIds = json_decode($planning->shops, true);
            $planning->shops = Route::whereIn('id', $shopIds)->get();
        }

        return response()->json($plannings);
    }

    public function getPlannings($userId)
    {
        $plannings = Planning::where('user_id', $userId)->get();

        // Check if the user has any planning records
        if ($plannings->isEmpty()) {
            return response()->json(['message' => 'No planning records found for the specified salesperson.'], 404);
        }

        foreach ($plannings as $planning) {
            $shopIds = json_decode($planning->shops, true);
            $planning->shops = Route::whereIn('id', $shopIds)->get();
        }

        return response()->json($plannings);
    }





}