<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\Point;

final class StoreController extends Controller
{
    public function create(StoreRequest $request): JsonResponse
    {
        try {
            $store = Store::create([
                'name' => $request->name,
                'coordinates' => new Point(
                    latitude: $request->latitude,
                    longitude: $request->longitude,
                ),
                'status' => $request->status,
                'store_type_id' => $request->type,
                'max_delivery_distance_in_meters' => $request->max_delivery_distance_in_meters,
            ]);

            if (is_null($store)) {
                return response()->json([
                    'message' => 'Failed to add store.',
                ], 500);
            }

            return response()->json([
                'message' => 'Store added successfully.',
                'data' => $store->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error adding store: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'An error occurred while adding the store.',
            ], 500);
        }
    }
}
