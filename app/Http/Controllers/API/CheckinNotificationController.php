<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CheckinNotification;
use App\Http\Controllers\Controller;

class CheckinNotificationController extends Controller
{
    public function index()
    {
        $checkinNotifications = CheckinNotification::orderBy('date', 'desc')->get();
    
        if ($checkinNotifications->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No check-in notifications found',
            ], 404);
        }
    
        $data = $checkinNotifications->map(function ($notification) {
            $salesmanName = User::where('id', $notification->salesman_id)->value('name'); // Fetch salesman name
            return [
                'id' => $notification->id,
                'salesman_id' => $notification->salesman_id,
                'salesman_name' => $salesmanName, 
                'street' => $notification->street,
                'city'=> $notification->city,
                'postcode'=>$notification->postcode,
                'images'=>$notification->images,
                'date' => $notification->date,
            ];
        });
    
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }
    
    
}
