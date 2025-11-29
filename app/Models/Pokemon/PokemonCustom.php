<?php

namespace App\Models\Pokemon;

use Illuminate\Database\Eloquent\Model;

class PokemonCustom extends Model
{
    protected $table = 'pokemons_custom';

    protected $fillable = [
        'name',
        'height',
        'weight',
        'types',
        'abilities',
    ];

    protected $casts = [
        'types' => 'array',
        'abilities' => 'array',
    ];
}
