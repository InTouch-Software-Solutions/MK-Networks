<?php

namespace App\Http\Controllers\API;


use App\Models\User;
use App\Models\Salesman;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

    public function destroy($id)
    {
        if (Auth::user() && Auth::user()->role === 'admin') {
            $salesman = Salesman::find($id);

            if (!$salesman) {
                return response()->json(['message' => 'Salesman not found.'], 404);
            }
            // Soft delete the salesman
            $salesman->delete();
            if ($salesman->user) {
                $salesman->user->delete(); // Soft delete the user
            }

            return response()->json(['message' => 'Salesman deleted successfully.'], 200);
        }

        return response()->json(['message' => 'Unauthorized. Only admins can delete salesmen.'], 403);
    }

}
