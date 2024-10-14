<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    
    
     public function saveproduct(Request $request)
    {

        $request->validate([
            'category_id' => 'required',
            'name' => 'required',
            'image' => 'required', // Image input should be an array
            'description' => 'required',
            'brand_name' => 'required',
            'price' => 'required',
        ]);



        $imagePaths = [];

        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move('ProductsImages/', $filename);
                $imagePaths[] = $filename;
            }
        } else {
            return response()->json(['error' => 'Image files are required.'], 400);
        }

        $product = Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'image' => json_encode($imagePaths), // Store the image paths as JSON
            'description' => $request->description,
            'brand_name' => $request->brand_name,
            'price' => $request->price,
            'dummy_price' => $request->dummy_price,
        ]);

        return response()->json($product, 201);
    }

  
    public function updateProduct(Request $request, $id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    $request->validate([
        'category_id' => 'required',
        'name' => 'required',
        'price' => 'required|numeric',
        'dummy_price' => 'nullable|numeric',
    ]);

    $imagePaths = json_decode($product->image, true) ?? []; // Retain existing images

    // Store and update image paths if new images are provided
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move('ProductsImages/', $filename);
            $imagePaths[] = $filename; // Append new images
        }
    }

    $product->update([
        'category_id' => $request->category_id,
        'name' => $request->name,
        'image' => json_encode($imagePaths), // Update image paths as JSON
        'description' => $request->description,
        'brand_name' => $request->brand_name,
        'price' => $request->price,
        'dummy_price' => $request->dummy_price,
    ]);

    return response()->json($product, 200);
}

    
    
    

    // Delete a product and its associated images
 public function deleteProduct($id)
{
    $product = Product::find($id);
    $product->delete();
    return response()->json(['message' => 'Product deleted successfully'], 200);
}

    
   public function getProduct()
{
    // Fetch all products
    $products = Product::all();
    
    // Decode the image paths for each product
    foreach ($products as $product) {
        $product->image = json_decode($product->image); // Decode JSON to array
    }

    return response()->json($products, 200);
}


}
