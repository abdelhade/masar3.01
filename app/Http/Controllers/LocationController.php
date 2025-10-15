<?php

namespace App\Http\Controllers;

use App\Models\UserLocationTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function storeTracking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'session_id' => 'required|string',
            'tracked_at' => 'required|date',
            'type' => 'nullable|string|in:login,tracking,attendance',
            'address' => 'nullable|string|max:500',
            'place_id' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $tracking = UserLocationTracking::create([
                'user_id' => Auth::id(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'session_id' => $request->session_id,
                'tracked_at' => $request->tracked_at,
                'type' => $request->type ?? 'tracking',
                'address' => $request->address,
                'place_id' => $request->place_id,
                'additional_data' => $request->additional_data ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location tracked successfully',
                'data' => $tracking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getHistory(Request $request)
    {
        $query = UserLocationTracking::where('user_id', Auth::id());

        if ($request->has('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('tracked_at', [$request->from, $request->to]);
        }

        $history = $query->orderBy('tracked_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json($history->items());
    }
}