<?php

namespace App\Http\Controllers\Pokemon;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheController extends Controller
{
    public function clearPokemon(string $name): JsonResponse
    {
        $cacheKey = 'pokemon:api:' . strtolower($name);
        $deleted = Cache::forget($cacheKey);

        if (!$deleted) {
            return response()->json([
                'message' => 'Cache key not found or already cleared',
                'key' => $cacheKey
            ], 404);
        }

        return response()->json([
            'message' => 'Cache cleared successfully',
            'pokemon' => $name,
            'key' => $cacheKey
        ]);
    }

    public function clearAll(): JsonResponse
    {
        $pattern = config('cache.prefix') . ':pokemon:api:*';
        $keys = Redis::keys($pattern);

        $count = 0;
        foreach ($keys as $key) {
            $keyWithoutPrefix = str_replace(config('cache.prefix') . ':', '', $key);
            Cache::forget($keyWithoutPrefix);
            $count++;
        }

        return response()->json([
            'message' => 'All pokemon cache cleared',
            'cleared_count' => $count
        ]);
    }
}
