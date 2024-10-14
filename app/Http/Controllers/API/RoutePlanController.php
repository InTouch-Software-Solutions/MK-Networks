<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssignRoute;
use App\Models\Route;
use App\Models\User;
use App\Models\Planning;
use Illuminate\Http\Request;
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

        // Store the file
        $filePath = $request->file('file')->store('uploads');


        // Load the file
        $spreadsheet = IOFactory::load(Storage::path($filePath));
        $worksheet = $spreadsheet->getActiveSheet();

        // Get all rows as an array
        $highestRow = $worksheet->getHighestRow();
        for ($row = 1; $row <= $highestRow; $row++) { // Start from 2 to skip header row
            $routeData = new Route();
            $routeData->shop = $worksheet->getCell('A' . $row)->getValue();
            $routeData->address = $worksheet->getCell('B' . $row)->getValue();
            $routeData->postcode = $worksheet->getCell('C' . $row)->getValue();
            $routeData->area = $worksheet->getCell('D' . $row)->getValue();
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
            'area' => 'required',
           
        ]);
        

        // Check if user is a salesperson
        $user = User::find($request->user_id);
        if (!$user || $user->role !== 'sales') {
            return response()->json(['message' => 'User does not exist or doesn\'t have the sales role'], 403);
        }

        // Create an associative array for areas and shops
        $areaShopAssignments = [];
        foreach ($request->area as $areaAssignment) {
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

       public function getPlannings()
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




}