<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Postcode;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class StoreAreaController extends Controller
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

            // Set the default search radius to 1km.
            $radius = $request->query('radius_in_meters', 1000);

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
                ->whereRaw("ST_Distance_Sphere(coordinates, POINT(?, ?)) <= ?", [
                    $postcodeRecord->coordinates->longitude,
                    $postcodeRecord->coordinates->latitude,
                    $radius,
                ])
                ->orderBy('distance')
                ->get();

            return response()->json([
                'message' => 'Stores near the postcode retrieved successfully.',
                'stores' => $stores,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error finding stores near the postcode: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'An error occurred while finding stores near the postcode.',
            ], 500);
        }
    }
}
