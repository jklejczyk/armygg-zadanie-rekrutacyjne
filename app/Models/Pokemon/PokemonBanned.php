<?php

namespace App\Models\Pokemon;

use Illuminate\Database\Eloquent\Model;

class PokemonBanned extends Model
{
    protected $table = 'pokemons_banned';

    protected $fillable = ['name'];
}
