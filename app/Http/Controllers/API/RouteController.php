<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Route;
use App\Models\Vendor;
use App\Models\Planning;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RouteController extends Controller
{
    // Get list of all cities
    public function getCities()
    {
        $cities = Route::distinct()->pluck('city')->toArray();
        return response()->json($cities);
    }

    // Get areas by selected city
    public function getAreas($city)
    {
        $areas = Route::where('city', $city)->distinct()->pluck('area')->toArray();
        return response()->json($areas);
    }

    // Get shops, addresses, and postcodes by selected area
    public function getShopsByArea($area)
    {
        $shopsData = Route::where('area', $area)
            ->distinct()
            ->get(['id', 'postcode', 'shop', 'address']);

        $totalShops = $shopsData->count();

        $response = [
            'totalShops' => $totalShops,
            'shops' => []
        ];


        foreach ($shopsData as $shop) {
            $response['shops'][] = [
                'id' => $shop->id,
                'postcode' => $shop->postcode,
                'shop' => $shop->shop,
                'address' => $shop->address,
            ];
        }

        return response()->json($response);
    }

    //get specific shop details by id
    public function getShopById($id)
    {
        $shop = Route::find($id);    
        if (!$shop) {
            return response()->json(['message' => 'Shop not found.'], 404);
        }    
        $vendor = Vendor::where('shop_id', $id)->pluck('user_id')->first();
        $vendor = $vendor !== null ? (int)$vendor : null;
        
        return response()->json([
            'shop_name' => $shop->shop,
            'postcode' => $shop->postcode,
            'area' => $shop->area,
            'address' => $shop->address,
            'vendor' => $vendor,
        ], 200);
    }
    

    public function routeHistory($city)
    {
        $allAreas = Route::where('city', $city)->distinct()->pluck('area')->toArray();
        $partiallyAssignedAreas = [];
        $completelyUnassignedAreas = [];
    
        foreach ($allAreas as $area) {
            // Get all routes (shops) in this area
            $allShops = Route::where('city', $city)
                ->where('area', $area)
                ->get();
                
            // Get all plannings for this area
            $plannings = Planning::whereJsonContains('area', [['area' => $area]])->get();
            
            // If no plannings exist, this is a completely unassigned area
            if ($plannings->isEmpty()) {
                $completelyUnassignedAreas[] = [
                    'area' => $area,
                    'total_shops' => count($allShops)
                ];
                continue;
            }
            
            $assignedShopIds = [];
            $salesmenForArea = [];
            
            foreach ($plannings as $planning) {
                $salesman = User::find($planning->user_id);
                if ($salesman) {
                    $salesmenForArea[] =  $salesman->name;
                    
                }
                $assignedShopIds = array_merge($assignedShopIds, json_decode($planning->shops, true) ?? []);
            }
            
            $assignedShopIds = array_unique($assignedShopIds);
            
            $assignedShops = [];
            $unassignedShops = [];
            
            foreach ($allShops as $shop) {
                $shopData = [
                    // 'id' => $shop->id,
                    'name' => $shop->shop,
                    'postcode' => $shop->postcode,
                    // 'address' => $shop->address
                ];
                
                if (in_array($shop->id, $assignedShopIds)) {
                    $assignedShops[] = $shopData;
                } else {
                    $unassignedShops[] = $shopData;
                }
            }
            
            $partiallyAssignedAreas[] = [
                'area' => $area,
                'total_shops' => count($allShops),
                'assigned_shops_count' => count($assignedShops),
                'unassigned_shops_count' => count($unassignedShops),
                'coverage_percentage' => (count($assignedShops) / count($allShops)) * 100,
                'salesmen' => $salesmenForArea,
                'assigned_shops' => $assignedShops,
                'unassigned_shops' => $unassignedShops
            ];
        }
    
        return response()->json([
            'city' => $city,
            'partially_assigned_areas' => $partiallyAssignedAreas,
            'completely_unassigned_areas' => $completelyUnassignedAreas
        ]);
    }
    
    
    
    
}
