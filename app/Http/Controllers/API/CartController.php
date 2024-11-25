<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $user = Auth::user();
        $product = Product::find($request->product_id);
    
        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();
    
        if ($cart) {
            $cart->quantity += $request->quantity; 
            $cart->total = $cart->quantity * (int)$product->price; 
        } else {
            $cart = new Cart();
            $cart->user_id = $user->id;
            $cart->product_id = $product->id;
            $cart->quantity = $request->quantity; 
            $cart->price = $product->price;
            $cart->total = $cart->quantity * (int)$product->price; 
            $cart->name = $product->name;
    
            $images = json_decode($product->image);
            $cart->image = $images[0] ?? null; 
        }
    
        $cart->save();
    
        return response()->json([
            'message' => 'Product added to cart successfully',
            'data' => $cart,
        ]);
    }
    

    public function index()
    {
        $user = Auth::user();
        $carts = Cart::where('user_id', $user->id)->get();

        // Decode the shops JSON string and fetch associated routes
        foreach ($carts as $cart) {
            $cart->detail = Product::find($cart->product_id);
        }

        return response()->json($carts);

    }

    public function destroy($id)
    {
        $cart = Cart::find($id);
        if($cart)
        {
        $cart->delete();
        return response()->json(['message' => 'Cart deleted successfully'], 200);
        }
        else
        return response()->json(['message' => 'Cart Not Found'], 200);
    }



}
