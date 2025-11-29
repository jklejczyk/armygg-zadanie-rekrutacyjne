<?php

use App\Http\Controllers\Pokemon\CacheController;
use App\Http\Controllers\Pokemon\PokemonBannedController;
use App\Http\Controllers\Pokemon\PokemonController;
use App\Http\Controllers\Pokemon\PokemonCustomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('pokemon')->group(function () {
    Route::get('/info', [PokemonController::class, 'getInfo']);

    Route::middleware('auth.secret')->group(function () {
        Route::get('/banned', [PokemonBannedController::class, 'index']);
        Route::post('/banned', [PokemonBannedController::class, 'store']);
        Route::delete('/banned/{name}', [PokemonBannedController::class, 'destroy']);

        Route::get('/custom-pokemon', [PokemonCustomController::class, 'index']);
        Route::post('/custom-pokemon', [PokemonCustomController::class, 'store']);
        Route::put('/custom-pokemon/{name}', [PokemonCustomController::class, 'update']);
        Route::delete('/custom-pokemon/{name}', [PokemonCustomController::class, 'destroy']);

        Route::prefix('cache')->group(function () {
            Route::delete('/clear', [CacheController::class, 'clearAll']);
            Route::delete('/clear/{name}', [CacheController::class, 'clearPokemon']);
        });
    });
});
