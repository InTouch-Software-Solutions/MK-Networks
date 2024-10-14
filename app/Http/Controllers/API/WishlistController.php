<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function addProductToWishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            
        ]);
        $user = Auth::user();
        $product = Product::find($request->product_id);
        // Create a new cart record
        $wishlist = Wishlist::where('user_id', $user->id)->where('product_id', $request->product_id)->first();
        if($wishlist){
            $wishlist->delete();
        }else{
            $wishlist = new Wishlist();
            $wishlist->user_id = $user->id;
            $wishlist->product_id = $product->id;
            $wishlist->price = $product->price;
            $wishlist->name = $product->name;
            $images = json_decode($product->image);
            $wishlist->image = $images[0];
        }
        $wishlist->save();

        return response()->json(['message' => 'Product added to Wishlist successfully', 'data' => $wishlist]);
    }

    public function viewWishlist()
    {
        $user = Auth::user();
        $wishlists = Wishlist::where('user_id', $user->id)->get();

        // Decode the shops JSON string and fetch associated routes
        foreach ($wishlists as $wishlist) {
            $wishlist->detail = Product::find($wishlist->product_id);
        }

        return response()->json($wishlists);

    }

    public function deleteWishlist($id)
    {
        $wishlist = Wishlist::findOrFail($id);
        $wishlist->delete();
        return response()->json(['message' => 'Wishlist deleted successfully'], 200);
    }

}
