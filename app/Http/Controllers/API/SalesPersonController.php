<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use App\Models\SalesExecutive;
use Illuminate\Http\Request;

class SalesPersonController extends Controller
{
    public function savesales(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required',
        ]);

        // Create a new salesperson using request input
        $salesperson = Sale::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
        ]);

        // Return the newly created salesperson as JSON
        return response()->json($salesperson, 200);
    }
    public function create_sales_executive(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
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
}
