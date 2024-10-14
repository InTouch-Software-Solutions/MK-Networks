<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SalesExecutive;

class SalesExecutiveContoller extends Controller
{
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
