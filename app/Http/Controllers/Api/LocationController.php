<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Region;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all regions.
     */
    public function regions()
    {
        $regions = Region::all();

        return response()->json([
            'success' => true,
            'data' => $regions,
        ]);
    }

    /**
     * Get all cities in a specific region.
     */
    public function cities($regionId)
    {
        $region = Region::find($regionId);

        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'Region not found.',
            ], 404);
        }

        $cities = $region->cities;

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }

    /**
     * Get all cities.
     */
    public function allCities()
    {
        $cities = City::all();

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }
}
