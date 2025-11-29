<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PokemonService
{
    public function __construct(
        protected PokemonCustomService $customService,
        protected PokemonBannedService $bannedService
    ) {}

    public function fetchPokemonData(string $name): ?array
    {
        $customPokemon = $this->customService->findByName(strtolower($name));

        if ($customPokemon) {
            return $this->customService->formatPokemonData($customPokemon);
        }

        return $this->fetchFromPokeAPI($name);
    }

    protected function fetchFromPokeAPI(string $name): ?array
    {
        $cacheKey = 'pokemon:api:' . strtolower($name);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$name}");

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            $formatted = [
                'name' => $data['name'],
                'height' => $data['height'],
                'weight' => $data['weight'],
                'types' => array_map(fn($type) => $type['type']['name'], $data['types']),
                'abilities' => array_map(fn($ability) => $ability['ability']['name'], $data['abilities']),
                'is_custom' => false,
            ];

            Cache::put($cacheKey, $formatted, $this->calculateCacheTTL());

            return $formatted;

        } catch (\Exception $e) {
            return null;
        }
    }

    protected function calculateCacheTTL(): int
    {
        $now = now()->timezone('Europe/Warsaw');

        $nextUpdate = $now->copy()->addDay()->setTime(12, 0, 0);

        if ($now->hour < 12) {
            $nextUpdate = $now->copy()->setTime(12, 0, 0);
        }

        return $nextUpdate->diffInSeconds($now);
    }

    public function filterPokemonBanned(array $pokemons): array
    {
        $bannedNames = $this->bannedService->getAllBanned();
        $bannedNamesLower = [];

        foreach ($bannedNames as $name) {
            $bannedNamesLower[] = strtolower($name);
        }

        $allowed = [];
        foreach ($pokemons as $pokemon) {
            if (!in_array(strtolower($pokemon), $bannedNamesLower)) {
                $allowed[] = $pokemon;
            }
        }

        return $allowed;
    }

    public function getBannedFromList(array $pokemons): array
    {
        $bannedNames = $this->bannedService->getAllBanned();
        $bannedNamesLower = [];

        foreach ($bannedNames as $name) {
            $bannedNamesLower[] = strtolower($name);
        }

        $filtered = [];
        foreach ($pokemons as $pokemon) {
            if (in_array(strtolower($pokemon), $bannedNamesLower)) {
                $filtered[] = $pokemon;
            }
        }

        return $filtered;
    }
}
