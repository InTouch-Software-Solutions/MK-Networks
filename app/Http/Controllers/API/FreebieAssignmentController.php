<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\VendorAssignment;
use App\Models\FreebieAssignment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FreebieAssignmentController extends Controller
{

    // Get all freebies assigned to salesmen by admin
    public function index()
    {
        $freebies = FreebieAssignment::all();
        if ($freebies->isEmpty()) {
            return response()->json(['message' => 'No freebies assigned yet.'], 404);
        }

        $freebieDetails = $freebies->map(function ($freebie) {
            $product = Product::find($freebie->product_id);
            $admin = User::find($freebie->assigned_by);
            $salesman = User::find($freebie->salesman_id);

            return [
                'id' => $freebie->id,
                // 'product_id' => $freebie->product_id,
                'product_name' => $product ? $product->name : 'Unknown Product',
                // 'salesman_id' => $freebie->salesman_id,
                'salesman_name' => $salesman ? $salesman->name : 'Unknown Salesman',
                'assigned_quantity' => $freebie->assigned_quantity,
                'sold_quantity' => $freebie->sold_quantity,
                'gifted_quantity' => $freebie->gifted_quantity,
                'remaining_quantity' => $freebie->remaining_quantity,
                'threshold' => $freebie->threshold,
                'assigned_by' => $admin ? $admin->name : 'Unknown Admin',
                'assigned_at' => Carbon::parse($freebie->assigned_at)->format('d-m-Y'),
            ];
        });

        return response()->json(['status' => 'success', 'data' => $freebieDetails], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'salesman_id' => 'required|integer',
            'assigned_quantity' => 'required|integer|min:1',
            'threshold' => 'nullable|integer|min:1',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }
    
        $validated = $validator->validated();
    
        $product = Product::find($validated['product_id']);
        if (!$product) {
            return response()->json(['error' => 'Invalid product ID.'], 400);
        }
    
        $salesman = User::find($validated['salesman_id']);
        if ($salesman->role !== 'sales') {
            return response()->json([
                'error' => 'Invalid salesman',
                'message' => 'The specified user is not a salesman.'
            ], 400);
        }
    
        $threshold = $validated['threshold'] ?? 5;
    
        // Check if a freebie assignment already exists
        $existingAssignment = FreebieAssignment::where('product_id', $validated['product_id'])
            ->where('salesman_id', $validated['salesman_id'])
            ->first();
    
        if ($existingAssignment) {
            // Update existing record
            $existingAssignment->assigned_quantity += $validated['assigned_quantity'];
            $existingAssignment->remaining_quantity += $validated['assigned_quantity'];
            $existingAssignment->save();
    
            return response()->json([
                'message' => 'Freebie quantity updated successfully.',
                'freebie_assignment' => $existingAssignment
            ], 200);
        } else {
            // Create new record
            $freebieAssignment = FreebieAssignment::create([
                'product_id' => $validated['product_id'],
                'salesman_id' => $validated['salesman_id'],
                'assigned_quantity' => $validated['assigned_quantity'],
                'remaining_quantity' => $validated['assigned_quantity'],
                'threshold' => $threshold,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]);
    
            return response()->json([
                'message' => 'Freebie assigned successfully.',
                'freebie_assignment' => $freebieAssignment
            ], 201);
        }
    }
    




    // Salesman assigns freebies or products to a vendor
    public function assignToVendor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'vendor_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|string|in:purchase,gift',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $product = Product::find($validated['product_id']);
        if (!$product) {
            return response()->json(['error' => 'Invalid product ID.'], 400);
        }

        $vendor = User::find($validated['vendor_id']);
        if (!$vendor || $vendor->role !== 'vendor') {
            return response()->json([
                'error' => 'Invalid vendor',
                'message' => 'The specified user is not a vendor.'
            ], 400);
        }

        $freebieAssignment = FreebieAssignment::where('product_id', $validated['product_id'])
            ->where('salesman_id', auth()->id())
            ->first();

        if (!$freebieAssignment) {
            return response()->json(['error' => 'Salesman has no inventory for this product.'], 404);
        }

        if ($freebieAssignment->remaining_quantity < $validated['quantity']) {
            return response()->json(['error' => 'Not enough inventory to assign this quantity.'], 400);
        }

        VendorAssignment::create([
            'product_id' => $validated['product_id'],
            'salesman_id' => auth()->id(),
            'vendor_id' => $validated['vendor_id'],
            'quantity' => $validated['quantity'],
            'type' => $validated['type'], // purchase or gift
            'transaction_date' => now(),
        ]);

        if ($validated['type'] == 'purchase') {
            $freebieAssignment->sold_quantity += $validated['quantity'];
        } elseif ($validated['type'] == 'gift') {
            $freebieAssignment->gifted_quantity += $validated['quantity'];
        }

        $freebieAssignment->remaining_quantity = $freebieAssignment->assigned_quantity - ($freebieAssignment->sold_quantity + $freebieAssignment->gifted_quantity);
        $freebieAssignment->save();

        if ($freebieAssignment->remaining_quantity < $freebieAssignment->threshold) {

        }

        return response()->json([
            'message' => 'Product assigned to vendor successfully.',
            'vendor_assignment' => $freebieAssignment
        ], 201);
    }

    public function salesmanInventory()
    {
        $salesman_id = auth()->id();
        $assignments = FreebieAssignment::where('salesman_id', $salesman_id)->get();

        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'No assigned products found for this salesman.'], 404);
        }

        $inventoryDetails = $assignments->map(function ($assignment) {
            $product = Product::find($assignment->product_id);
            $assignedBy = User::find($assignment->assigned_by);
            $vendorAssignments = VendorAssignment::where('product_id', $assignment->product_id)
                ->where('salesman_id', $assignment->salesman_id)
                ->get();

            $vendorDetails = $vendorAssignments->map(function ($vendorAssignment) {
                $vendor = User::find($vendorAssignment->vendor_id);

                return [
                    'vendor_name' => $vendor ? $vendor->name : 'Unknown Vendor',
                    'quantity' => $vendorAssignment->quantity,
                    'type' => $vendorAssignment->type,
                ];
            });

            return [
                'product_name' => $product ? $product->name : 'Unknown Product',
                'assigned_quantity' => $assignment->assigned_quantity,
                'sold_quantity' => $assignment->sold_quantity,
                'gifted_quantity' => $assignment->gifted_quantity,
                'remaining_quantity' => $assignment->remaining_quantity,
                'threshold' => $assignment->threshold,
                'assigned_by' => $assignedBy ? $assignedBy->name : 'Unknown Admin',
                'assigned_at' => Carbon::parse($assignment->assigned_at)->format('d-m-Y'),
                'vendor_list' => $vendorDetails
            ];
        });

        return response()->json([
            'message' => 'Salesman inventory retrieved successfully.',
            'inventory' => $inventoryDetails
        ], 200);
    }


    //vendor can view its inventory
    public function vendorInventory()
    {
        $vendor_id = auth()->id();

        $assignments = VendorAssignment::where('vendor_id', $vendor_id)->get();
        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'No inventory found for this vendor.'], 404);
        }
        $inventoryDetails = $assignments->map(function ($assignment) {
            $product = Product::find($assignment->product_id);

            return [
                // 'product_id' => $assignment->product_id,
                'product_name' => $product ? $product->name : 'Unknown Product',
                'quantity' => $assignment->quantity,
                'type' => $assignment->type,
                'transaction_date' => Carbon::parse($assignment->transaction_date)->format('d-m-Y'),
            ];
        });

        return response()->json([
            'message' => 'Vendor inventory retrieved successfully.',
            'inventory' => $inventoryDetails
        ], 200);
    }

}