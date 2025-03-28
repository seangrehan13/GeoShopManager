<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Postcode;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class StoreDeliveryController extends Controller
{
    public function show(Request $request, string $postcode): JsonResponse
    {
        try {
            $normalizedPostcode = strtoupper(str_replace(' ', '', $postcode));

            $postcodeRecord = Postcode::find($normalizedPostcode);

            if (is_null($postcodeRecord)) {
                return response()->json([
                    'message' => 'Postcode not found.',
                ], 404);
            }

            $stores = Store::query()
                ->select([
                    'id',
                    'name',
                    'coordinates',
                    'status',
                    'store_type_id AS type',
                    'max_delivery_distance_in_meters',
                ])
                ->selectRaw("ST_Distance_Sphere(coordinates, POINT(?, ?)) AS distance", [
                    $postcodeRecord->coordinates->longitude,
                    $postcodeRecord->coordinates->latitude,
                ])
                ->whereRaw("ST_Distance_Sphere(coordinates, POINT(?, ?)) <= max_delivery_distance_in_meters", [
                    $postcodeRecord->coordinates->longitude,
                    $postcodeRecord->coordinates->latitude,
                ])
                ->orderBy('distance')
                ->get();

            return response()->json([
                'message' => 'Stores that can deliver to the postcode retrieved successfully.',
                'stores' => $stores,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error finding stores that can deliver to the postcode: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'An error occurred while finding stores that can deliver to the postcode.',
            ], 500);
        }
    }
}
