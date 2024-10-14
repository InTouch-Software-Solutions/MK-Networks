<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CheckOut;
use App\Models\Product;
use Illuminate\Http\Request;

class CheckOutController extends Controller
{


    public function saveorder(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'zipcode' => 'required',
            'product_details' => 'required',
        ]);

        // Create a new order using request input
        $orders = CheckOut::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'country' => $request->input('country'),
            'zipcode' => $request->input('zipcode'),
            'product_details' => json_encode($request->product_details) // Ensure this is in JSON format
        ]);

        // Return the newly created order as JSON
        return response()->json($orders, 200);
    }

    public function viewCheckout()
    {
        $checkouts = CheckOut::all();

        // Decode the shops JSON string and fetch associated routes
        foreach ($checkouts as $checkout) {
            $checkoutIds = json_decode($checkout->product_details, true);
            $checkout->product_details = Product::whereIn('id', $checkoutIds)->get();
        }

        return response()->json($checkouts);

    }

}

