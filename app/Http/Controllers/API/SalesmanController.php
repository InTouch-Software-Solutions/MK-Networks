<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SalesmanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|max:255',
            'area' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'sales',
        ]);

        $salesman = $user->salesman()->create([
            'phone_number' => $request->phone_number,
            'area' => $request->area,
        ]);

        return response()->json(['message' => 'Salesman created successfully', 'salesman' => $salesman], 201);
    }

    public function index()
    {
        $salesmen = Salesman::with('user')->get();
        return response()->json(['salesmen' => $salesmen]);
    }
}
