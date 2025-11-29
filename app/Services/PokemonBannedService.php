<?php

namespace App\Services;

use App\Models\Pokemon\PokemonBanned;

class PokemonBannedService
{
    public function getAllBanned(): array
    {
        return PokemonBanned::pluck('name')->toArray();
    }

    public function addToBanned(string $name): void
    {
        PokemonBanned::create(['name' => $name]);
    }

    public function removeFromBanned(string $name): bool
    {
        return PokemonBanned::where('name', $name)->delete();
    }

}
