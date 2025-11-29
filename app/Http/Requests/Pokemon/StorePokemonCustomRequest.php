<?php

namespace App\Http\Requests\Pokemon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Validator;

class StorePokemonCustomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:pokemons_custom,name',
            'height' => 'required|integer|min:1',
            'weight' => 'required|integer|min:1',
            'types' => 'required|array|min:1',
            'types.*' => 'string',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'string',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $name = $this->input('name');

            // Check if pokemon exists in PokeAPI
            try {
                $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$name}");
                if ($response->successful()) {
                    $validator->errors()->add('name', 'This pokemon already exists in PokeAPI.');
                }
            } catch (\Exception $e) {
                // If there's an error fetching, we assume it doesn't exist
            }
        });
    }
}
