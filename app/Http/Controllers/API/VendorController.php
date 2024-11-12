<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class VendorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'shop' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'postcode' => 'required|string|max:10',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'vendor',
        ]);

        $vendor = $user->vendor()->create([
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'shop' => $request->shop,
            'area' => $request->area,
            'postcode' => $request->postcode,
        ]);

        return response()->json(['message' => 'Vendor created successfully', 'vendor' => $vendor], 201);
    }

    public function index()
    {
        $vendors = Vendor::with('user')->get();
        return response()->json(['vendors' => $vendors]);
    }

    public function destroy($id)
    {
        if (Auth::user() && Auth::user()->role !== 'vendor') {
            $vendor = Vendor::find($id);


            if (!$vendor) {
                return response()->json(['message' => 'Vendor not found.'], 404);
            }

            // Soft delete the vendor
            $vendor->delete();

            if ($vendor->user) {
                $vendor->user->delete(); // Soft delete the user
            }

            return response()->json(['message' => 'Vendor soft deleted successfully.'], 200);
        }

        return response()->json(['message' => 'Unauthorized.'], 403);
    }

    public function uploadImages(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $vendor = Vendor::where('user_id', Auth::id())->first();

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        if ($request->hasFile('images')) {
            $imageNames = [];
            $existingImages = $vendor->images ?? [];

            foreach ($request->file('images') as $image) {
                $imageName = 'vendor_shop_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/vendors'), $imageName);
                $imageNames[] = $imageName;
            }

            $vendor->update([
                'images' => array_merge($existingImages, $imageNames)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Images added successfully',
                'data' => [
                    'images' => $vendor->images,
                    'shop' => $vendor->shop,
                    'area' => $vendor->area,
                    'postcode' => $vendor->postcode,
                    'address' => $vendor->address,
                    'updated_at' => $vendor->updated_at->format('d-m-Y')
                ]
            ], 200);
        }

        return response()->json(['message' => 'Image upload failed'], 500);
    }

}
