<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SalesExecutive;
use App\Models\Vendor;

class VendorController extends Controller
{
    //
    public function savevendor(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:vendors,email',
            'number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'shop' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'postcode' => 'required|string|max:255',
        ]);

        // Create a new vendor record
        $vendor = Vendor::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $vendor,
            'message' => 'Vendor created successfully'
        ], 201);
    }
    
    public function viewvendor()
    {
        // Fetch all vendors
        $vendors = Vendor::all();

        return response()->json([
            'status' => 'success',
            'data' => $vendors,
            'message' => 'Vendors fetched successfully'
        ]);
    }
    public function create_sales_executive(Request $request)
    {
        $request->validate([
        'name' => 'required',
        'email' => 'required|email|max:255',
        'phone_number' => 'required',
        'area' => 'required',
        ]);
    
        $user=new User();
        $user->name=$request->name;
        $user->email=$request->email;
        $user->role="sales";
        $user->password=Hash::make('12345678');
        $user->save();
        
        
        $se=new SalesExecutive();
        $se->phone_number=$request->phone_number;
        $se->area=$request->area;
        $se->user_id=$user->id;
        $se->save();
        $se->user=$user;

        return response()->json(['message' => 'Created successfully!','data'=>$se ], 200);
    }
    public function get_sales_executive()
    {
        // Fetch all vendors
        $se = SalesExecutive::all();
        foreach($se as $s)
        {
            $user=User::find($s->user_id);
            $s->name=$user->name;
            $s->email=$user->email;
            $s->role=$user->role;
        }

        return response()->json([
            'status' => 'success',
            'data' => $se,
            'message' => 'Sales Executive fetched successfully'
        ]);
    }
}
