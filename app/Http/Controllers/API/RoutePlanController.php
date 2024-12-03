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
            'city' => 'required|string',
            'file' => 'required|mimes:xlsx,csv|max:2048',
        ]);

        $city = $request->input('city');

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
            // $routeData->city = $worksheet->getCell('E' . $row)->getValue();
            $routeData->city = $city;
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



    public function checkAssignedShops(Request $request)
    {
        $requestedShops = collect($request->area)
            ->pluck('shops')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();



        $assignedShops = [];

        foreach ($requestedShops as $shopId) {
            $existingAssignments = Planning::whereRaw('JSON_CONTAINS(shops, ?)', [json_encode([$shopId])])
                ->get();

            foreach ($existingAssignments as $assignment) {
                $userName = User::find($assignment->user_id)->name ?? 'Unknown';

                $assignedShops[] = [
                    'shop_id' => $shopId,
                    'assigned_to' => $userName
                ];
            }
        }

        return response()->json([
            'has_assigned_shops' => !empty($assignedShops),
            'assigned_shops' => $assignedShops
        ]);
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

        if (!$request->force) {
            $assignedShopsResponse = $this->checkAssignedShops($request)->getData();
            $hasAssignedShops = $assignedShopsResponse->has_assigned_shops;

            if ($hasAssignedShops) {
                return response()->json([
                    'message' => 'Some shops are already assigned. Set force=true to override.',
                    'requires_confirmation' => true,
                    // 'assigned_shops' => $assignedShopsResponse->assigned_shops, 
                ], 409);
            }
        }


        $route = Planning::create([
            'user_id' => $request->user_id,
            'date' => $date,
            'area' => json_encode($areaShopAssignments),
            'shops' => json_encode(array_merge(...array_column($areaShopAssignments, 'shops'))), // Flatten all shop IDs and store
        ]);

        return response()->json(['message' => 'Shops assigned successfully', 'data' => $route]);
    }


    public function getAllPlannings(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $date = $request->input('date');

        $planningsQuery = $date
            ? Planning::where('date', $date)
            : Planning::query();

        $plannings = $planningsQuery->orderBy('date', 'desc')->get();

        $response = [
            'date' => $date ?? 'All Dates',
            'total_plannings' => $plannings->count(),
            'plannings' => []
        ];

        foreach ($plannings as $planning) {
            $planningDate = Carbon::parse($planning->date)->format('d-m-Y');
            $user = User::where('id', $planning->user_id)->select('name')->first();

            $areaAssignments = json_decode($planning->area, true);
            $areas = [];
            $areaShopCount = 0;

            foreach ($areaAssignments as $assignment) {

                $shops = Route::whereIn('id', $assignment['shops'])
                    ->get(['shop', 'address', 'postcode']);

                $shopCount = $shops->count();
                $areaShopCount += $shopCount;

                $areas[] = [
                    'area' => $assignment['area'],
                    'total_shops_areawise' => $shopCount,
                    'shops' => $shops
                ];
            }


            $response['plannings'][] = [
                'date' => $planningDate,
                'salesman' => $user->name ?? 'Unknown',
                'total_shops' => $areaShopCount,
                'areas' => $areas
            ];
        }

        return response()->json($response);
    }





    //get specific planning for a salesman
    public function getPlannings($userId, Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $date = $request->input('date');
        $query = Planning::where('user_id', $userId);

        if ($date) {
            $query->where('date', $date);
        }

        $plannings = $query->orderBy('date', 'desc')->get();

        if ($plannings->isEmpty()) {
            return response()->json(['message' => 'No planning records found for the specified salesperson.'], 404);
        }

        $response = [
            // 'date' => $date ?? 'All dates',
            'total_shops'=> 0,
            'areas' => [],
        ];

        foreach ($plannings as $planning) {
            $areaAssignments = json_decode($planning->area, true);
            $areaShopCount = 0;

            foreach ($areaAssignments as $assignment) {
                $planningDate = Carbon::parse($planning->date)->format('d-m-Y');
                $shops = Route::whereIn('id', $assignment['shops'])->get(['shop', 'address', 'postcode']);

                $shopCount = $shops->count();
                $areaShopCount += $shopCount;

                $response['areas'][] = [
                    'date' => $planningDate,
                    'area' => $assignment['area'],
                    'areawise_shops'=>$shopCount,
                    'shops' => $shops
                ];

                
            }
            $response['total_shops'] += $areaShopCount;
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