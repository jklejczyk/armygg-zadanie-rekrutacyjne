<?php

namespace App\Services;

use App\Models\Pokemon\PokemonCustom;
use Illuminate\Support\Collection;

class PokemonCustomService
{
    public function getAllCustomPokemons(): Collection
    {
        return PokemonCustom::all();
    }

    public function createCustomPokemon(array $data): PokemonCustom
    {
        return PokemonCustom::create($data);
    }

    public function findByName(string $name): ?PokemonCustom
    {
        return PokemonCustom::where('name', $name)->first();
    }

    public function updateCustomPokemon(PokemonCustom $pokemon, array $data): PokemonCustom
    {
        $pokemon->update($data);
        return $pokemon->fresh();
    }

    public function deleteByName(string $name): bool
    {
        return PokemonCustom::where('name', $name)->delete() > 0;
    }

    public function formatPokemonData(PokemonCustom $pokemon): array
    {
        return [
            'name' => $pokemon->name,
            'height' => $pokemon->height,
            'weight' => $pokemon->weight,
            'types' => $pokemon->types,
            'abilities' => $pokemon->abilities,
            'is_custom' => true,
        ];
    }
}
