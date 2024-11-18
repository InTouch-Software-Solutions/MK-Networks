<?php

namespace App\Http\Controllers\API;

use App\Models\Route;
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

        if ($shop) {
            return response()->json([
                'shop_name' => $shop->shop,
                'postcode' => $shop->postcode,
                'area' => $shop->area,
                'address' => $shop->address,
            ], 200);
        } else {
            return response()->json(['message' => 'Shop not found.'], 404);
        }
    }

}
