<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pokemons_custom', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('height');
            $table->integer('weight');
            $table->json('types');
            $table->json('abilities');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemons_custom');
    }
};
