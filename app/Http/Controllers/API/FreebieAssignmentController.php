<?php

namespace App\Http\Controllers\API;



use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\FreebieAssignment;

class FreebieAssignmentController extends Controller
{

    // Get all freebies assigned to salesmen
    public function index()
    {
        $freebies = FreebieAssignment::all();

        if ($freebies->isEmpty()) {
            return response()->json(['message' => 'No freebies assigned yet.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $freebies], 200);
    }
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'salesman_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Check if the product exists
        $product = Product::find($validated['product_id']);
        if (!$product) {
            return response()->json(['error' => 'Invalid product ID.'], 400);
        }

        // Check if the salesman has the role 'sales'
        $salesman = User::find($validated['salesman_id']);
        if ($salesman->role !== 'sales') {
            return response()->json([
                'error' => 'Invalid salesman',
                'message' => 'The specified user is not a salesman.'
            ], 400);
        }

        // Create the FreebieAssignment record
        $freebieAssignment = FreebieAssignment::create([
            'product_id' => $validated['product_id'],
            'salesman_id' => $validated['salesman_id'],
            'quantity' => $validated['quantity'],
        ]);

        return response()->json([
            'message' => 'Freebie assigned successfully.',
            'freebie_assignment' => $freebieAssignment
        ], 201);
    }


    // Get freebies assigned to a specific salesman by ID
    public function show($id)
    {
        // Check if the salesman exists and has the role 'sales'
        $salesman = User::find($id);
        if (!$salesman || $salesman->role !== 'sales') {
            return response()->json([
                'error' => 'Invalid salesman',
                'message' => 'The specified user is not a salesman.'
            ], 400);
        }

        // Retrieve freebie assignments for the given salesman ID
        $freebies = FreebieAssignment::where('salesman_id', $id)->get();

        if ($freebies->isEmpty()) {
            return response()->json([
                'message' => 'No freebies assigned to this salesman.'
            ], 404);
        }

        return response()->json([
            'message' => 'Freebies retrieved successfully.',
            'freebies' => $freebies
        ], 200);
    }
}