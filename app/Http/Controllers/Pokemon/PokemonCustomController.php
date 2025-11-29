<?php

namespace App\Http\Controllers\Pokemon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pokemon\StorePokemonCustomRequest;
use App\Http\Requests\Pokemon\UpdatePokemonCustomRequest;
use App\Services\PokemonCustomService;
use Illuminate\Http\JsonResponse;

class PokemonCustomController extends Controller
{
    public function __construct(
        protected PokemonCustomService $customService
    ) {}

    public function index(): JsonResponse
    {
        $pokemons = $this->customService->getAllCustomPokemons()
            ->map(fn($pokemon) => $this->customService->formatPokemonData($pokemon));

        return response()->json([
            'pokemons' => $pokemons
        ]);
    }

    public function store(StorePokemonCustomRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pokemon = $this->customService->createCustomPokemon($validated);

        return response()->json([
            'message' => 'Custom pokemon created successfully',
            'pokemon' => $this->customService->formatPokemonData($pokemon)
        ], 201);
    }

    public function update(UpdatePokemonCustomRequest $request, string $name): JsonResponse
    {
        $pokemon = $this->customService->findByName($name);

        if (!$pokemon) {
            return response()->json([
                'error' => 'Custom pokemon not found'
            ], 404);
        }

        $validated = $request->validated();
        $updatedPokemon = $this->customService->updateCustomPokemon($pokemon, $validated);

        return response()->json([
            'message' => 'Custom pokemon updated successfully',
            'pokemon' => $this->customService->formatPokemonData($updatedPokemon)
        ]);
    }

    public function destroy(string $name): JsonResponse
    {
        $deleted = $this->customService->deleteByName($name);

        if (!$deleted) {
            return response()->json([
                'error' => 'Custom pokemon not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Custom pokemon deleted successfully'
        ]);
    }
}
