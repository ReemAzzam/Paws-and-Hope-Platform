<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{

  public function store(Request $request)
{
    $validated = $request->validate([
        'token' => 'required|string',
        'device_name' => 'nullable|string'
    ]);

    $request->user()
        ->fcmTokens()
        ->updateOrCreate(
            ['token' => $validated['token']],
            [
                'device_name' => $validated['device_name'] ?? null
            ]
        );

    return response()->json([
        'message' => 'Token saved successfully'
    ]);

    
   }
}
