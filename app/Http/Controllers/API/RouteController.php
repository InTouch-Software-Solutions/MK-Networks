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

    // Get postcodes by selected area
    public function getPostcodes($area)
    {
        $postcodes = Route::where('area', $area)->distinct()->pluck('postcode')->toArray();
        return response()->json($postcodes);
    }

    // Get shops by selected postcode
    public function getShops($postcode)
    {
        $shops = Route::where('postcode', $postcode)->distinct()->get(['shop', 'address']);
        return response()->json($shops);
    }
}
