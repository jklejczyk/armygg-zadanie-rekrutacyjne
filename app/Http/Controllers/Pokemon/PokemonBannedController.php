<?php

namespace App\Http\Controllers\Pokemon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pokemon\StorePokemonBannedRequest;
use App\Services\PokemonBannedService;
use Illuminate\Http\JsonResponse;

class PokemonBannedController extends Controller
{
    public function __construct(
        protected PokemonBannedService $bannedService
    ) {}

    public function index(): JsonResponse
    {
        $banned = $this->bannedService->getAllBanned();

        return response()->json([
            'banned' => $banned
        ]);
    }

    public function store(StorePokemonBannedRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->bannedService->addToBanned($validated['name']);

        return response()->json([
            'message' => 'Pokemon added to banned list',
            'name' => $validated['name']
        ]);
    }

    public function destroy(string $name): JsonResponse
    {
        $deleted = $this->bannedService->removeFromBanned($name);

        if (!$deleted) {
            return response()->json([
                'error' => 'Pokemon not found in banned list'
            ], 404);
        }

        return response()->json([
            'message' => 'Pokemon removed from banned list'
        ]);
    }
}
