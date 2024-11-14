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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;



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



    // admin assign daily route plan to salesman 
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'date' => 'nullable|date',
            'area' => 'required|array',
            'area.*.area' => 'required|string',
            'area.*.shops' => 'required|array',
            'area.*.shops.*' => 'integer',

        ]);


        $user = User::find($request->user_id);
        if (!$user || $user->role !== 'sales') {
            return response()->json(['message' => 'User does not exist or doesn\'t have the sales role'], 403);
        }

        $date = $request->date ?? Carbon::today()->format('Y-m-d');


        $areaShopAssignments = [];
        foreach ($request->area as $areaAssignment) {

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
            'date' => $date,
            'area' => json_encode($areaShopAssignments),
            'shops' => json_encode(array_merge(...array_column($areaShopAssignments, 'shops'))), // Flatten all shop IDs and store
        ]);

        return response()->json(['message' => 'Shops assigned successfully', 'data' => $route]);
    }

    public function getAllPlannings(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->format('Y-m-d');
        $plannings = Planning::where('date', $date)->get();    
        $response = [
            'date' => $date,
            'plannings' => [] 
        ];
    
        foreach ($plannings as $planning) {
            $user = User::where('id', $planning->user_id)->select('name')->first();
            $areaAssignments = json_decode($planning->area, true);
            $areas = [];
    
            foreach ($areaAssignments as $assignment) {
                $shops = Route::whereIn('id', $assignment['shops'])->get(['shop', 'address']);    
                $areas[] = [
                    'area' => $assignment['area'], 
                    'shops' => $shops 
                ];
            }
            $response['plannings'][] = [
                'salesman' => $user->name ?? 'Unknown', 
                'areas' => $areas
            ];
        }
        return response()->json($response);
    }
    
    
    
    //get specific planning
    public function getPlannings($userId, Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->format('Y-m-d');
        $plannings = Planning::where('user_id', $userId)->where('date', $date)->get();
        if ($plannings->isEmpty()) {
            return response()->json(['message' => 'No planning records found for the specified salesperson on this date.'], 404);
        }
        $response = [
            'date' => $date,
            'areas' => []
        ];
    
        foreach ($plannings as $planning) {
            $areaAssignments = json_decode($planning->area, true);
    
            foreach ($areaAssignments as $assignment) {
                $shops = Route::whereIn('id', $assignment['shops'])->get(['shop', 'address']);
                $response['areas'][] = [
                    'area' => $assignment['area'], 
                    'shops' => $shops 
                ];
            }
        }
        return response()->json($response);
    }
    
    public function getSalesmanPlannings(Request $request)
{
    // Get the authenticated user's ID
    $userId = Auth::id();

    // Check if the authenticated user is a salesperson
    $user = Auth::user();
    if (!$user || $user->role !== 'sales') { // Assuming 'role' is a column in the users table
        return response()->json(['message' => 'Unauthorized. User doesnot have a salesman role.'], 403);
    }

    $date = $request->input('date') ?? Carbon::today()->format('Y-m-d');
    $plannings = Planning::where('user_id', $userId)->where('date', $date)->get();

    if ($plannings->isEmpty()) {
        return response()->json(['message' => 'No planning records found on this date.'], 404);
    }

    $response = [
        'date' => $date,
        'areas' => []
    ];

    foreach ($plannings as $planning) {
        $areaAssignments = json_decode($planning->area, true);

        foreach ($areaAssignments as $assignment) {
            $shops = Route::whereIn('id', $assignment['shops'])->get(['shop', 'address']);
            $response['areas'][] = [
                'area' => $assignment['area'], 
                'shops' => $shops 
            ];
        }
    }

    return response()->json($response);
}

    





}