<?php

namespace App\Http\Controllers\Pokemon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pokemon\GetPokemonInfoRequest;
use App\Services\PokemonService;
use Illuminate\Http\JsonResponse;

class PokemonController extends Controller
{
    protected PokemonService $pokemonService;

    public function __construct(PokemonService $pokemonService)
    {
        $this->pokemonService = $pokemonService;
    }

    public function getInfo(GetPokemonInfoRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $pokemonNames = $validated['pokemon'];

        // Filter out banned pokemons
        $filtered = $this->pokemonService->getBannedFromList($pokemonNames);
        $allowedPokemons = $this->pokemonService->filterPokemonBanned($pokemonNames);

        $pokemons = [];
        $errors = [];

        foreach ($allowedPokemons as $name) {
            $data = $this->pokemonService->fetchPokemonData($name);

            if ($data === null) {
                $errors[] = $name;
            } else {
                $pokemons[] = $data;
            }
        }

        return response()->json([
            'pokemons' => $pokemons,
            'filtered' => $filtered,
            'errors' => $errors,
        ]);
    }
}
