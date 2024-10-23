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
            ->get(['postcode', 'shop', 'address']);

        $response = [];
        foreach ($shopsData as $shop) {
            $response[] = [
                'postcode' => $shop->postcode,
                'shop' => $shop->shop,
                'address' => $shop->address,
            ];
        }

        return response()->json($response);
    }

}
