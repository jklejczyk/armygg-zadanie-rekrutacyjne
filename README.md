# Pokemon API - Laravel REST API

REST API do zarządzania Pokemonami z integracją z PokeAPI. Projekt stworzony jako zadanie rekrutacyjne.

## Instalacja i uruchomienie

### Opcja 1: Docker (Rekomendowane)

**Wymagania:**
- Docker
- Docker Compose

```bash
# Sklonuj repozytorium
git clone https://github.com/jklejczyk/armygg-zadanie-rekrutacyjne
cd armygg-zadanie-rekrutacyjne

# Skopiuj plik konfiguracyjny
cp .env.example .env

# WAŻNE: Upewnij się, że SUPER_SECRET_KEY jest ustawiony w .env
# Domyślnie: SUPER_SECRET_KEY=tajne-haslo-123

# Zbuduj i uruchom kontenery
docker compose up -d --build

# Zainstaluj zależności
docker compose exec app composer install

# Wygeneruj klucz aplikacji
docker compose exec app php artisan key:generate

# Uruchom migracje
docker compose exec app php artisan migrate
```

**API będzie dostępne pod adresem:** `http://localhost:8000`

**Usługi:**
- **API**: http://localhost:8000
- **MariaDB**: localhost:3307
- **Redis**: localhost:6380

**Konfiguracja:**

Plik `.env` zawiera już domyślne wartości:
- `SUPER_SECRET_KEY=tajne-haslo-123` - klucz do chronionych endpointów
- `DB_DATABASE=pokemon_api` - nazwa bazy danych
- `DB_USERNAME=laravel_user` - użytkownik bazy
- `DB_PASSWORD=secret` - hasło do bazy

**Zatrzymanie kontenerów:**
```bash
docker compose down
```

## Dokumentacja API

### Publiczne endpointy

#### GET /api/pokemon/info
Pobiera informacje o Pokémonach z PokeAPI (filtruje zakazane).

**Parametry query:**
- `pokemon[]` (array, required) - lista nazw Pokémonów

**Przykład:**
```bash
curl "http://localhost:8000/api/pokemon/info?pokemon[]=pikachu&pokemon[]=charizard&pokemon[]=mewtwo"
```

**Odpowiedź 200:**
```json
{
  "pokemons": [
    {
      "name": "pikachu",
      "height": 4,
      "weight": 60,
      "types": ["electric"],
      "abilities": ["static", "lightning-rod"],
      "is_custom": false
    },
    {
      "name": "charizard",
      "height": 17,
      "weight": 905,
      "types": ["fire", "flying"],
      "abilities": ["blaze", "solar-power"],
      "is_custom": false
    }
  ],
  "filtered": ["mewtwo"],
  "errors": []
}
```

**Kody błędów:**
- `422` - Błąd walidacji (brak parametru pokemon lub nieprawidłowy format)

---

### Chronione endpointy (wymagają nagłówka X-SUPER-SECRET-KEY)

Wszystkie poniższe endpointy wymagają nagłówka:
```
X-SUPER-SECRET-KEY: tajne-haslo-123
```

Brak lub nieprawidłowy klucz zwróci:
```json
{
  "error": "Unauthorized"
}
```
**Kod błędu:** `401`

---

#### GET /api/pokemon/banned
Zwraca listę wszystkich zakazanych Pokémonów.

**Przykład:**
```bash
curl -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  http://localhost:8000/api/pokemon/banned
```

**Odpowiedź 200:**
```json
{
  "banned": ["mewtwo", "pikachu"]
}
```

---

#### POST /api/pokemon/banned
Dodaje Pokémona do listy zakazanych.

**Parametry body (JSON):**
- `name` (string, required) - nazwa Pokémona

**Przykład:**
```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  -d '{"name":"mewtwo"}' \
  http://localhost:8000/api/pokemon/banned
```

**Odpowiedź 200:**
```json
{
  "message": "Pokemon added to banned list",
  "name": "mewtwo"
}
```

**Kody błędów:**
- `422` - Błąd walidacji (brak name lub nieprawidłowy format)
- `401` - Brak autoryzacji

---

#### DELETE /api/pokemon/banned/{name}
Usuwa Pokémona z listy zakazanych.

**Parametry URL:**
- `name` (string) - nazwa Pokémona do usunięcia

**Przykład:**
```bash
curl -X DELETE \
  -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  http://localhost:8000/api/pokemon/banned/mewtwo
```

**Odpowiedź 200:**
```json
{
  "message": "Pokemon removed from banned list"
}
```

**Kody błędów:**
- `404` - Pokemon nie znaleziony na liście zakazanych
- `401` - Brak autoryzacji

---

#### GET /api/pokemon/custom-pokemon
Zwraca listę wszystkich własnych (custom) Pokémonów.

**Przykład:**
```bash
curl -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  http://localhost:8000/api/pokemon/custom-pokemon
```

**Odpowiedź 200:**
```json
{
  "pokemons": [
    {
      "name": "superpokemon",
      "height": 20,
      "weight": 1000,
      "types": ["fire", "electric"],
      "abilities": ["super-power", "mega-strike"],
      "is_custom": true
    }
  ]
}
```

---

#### POST /api/pokemon/custom-pokemon
Dodaje nowego własnego Pokémona.

**Parametry body (JSON):**
- `name` (string, required) - nazwa Pokémona
- `height` (integer, required, min:1) - wysokość
- `weight` (integer, required, min:1) - waga
- `types` (array, required, min:1) - typy Pokémona
- `abilities` (array, required, min:1) - umiejętności

**Walidacja:**
- Nazwa musi być unikalna w bazie custom pokemonów
- Nazwa nie może już istnieć w PokeAPI

**Przykład:**
```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  -d '{
    "name": "superpokemon",
    "height": 20,
    "weight": 1000,
    "types": ["fire", "electric"],
    "abilities": ["super-power", "mega-strike"]
  }' \
  http://localhost:8000/api/pokemon/custom-pokemon
```

**Odpowiedź 201:**
```json
{
  "message": "Custom pokemon created successfully",
  "pokemon": {
    "name": "superpokemon",
    "height": 20,
    "weight": 1000,
    "types": ["fire", "electric"],
    "abilities": ["super-power", "mega-strike"],
    "is_custom": true
  }
}
```

**Kody błędów:**
- `422` - Błąd walidacji (brakujące pola, nieprawidłowy format, nazwa już istnieje)
- `401` - Brak autoryzacji

---

#### PUT /api/pokemon/custom-pokemon/{name}
Aktualizuje istniejącego własnego Pokémona.

**Parametry URL:**
- `name` (string) - nazwa Pokémona do aktualizacji

**Parametry body (JSON) - wszystkie opcjonalne:**
- `height` (integer, optional, min:1)
- `weight` (integer, optional, min:1)
- `types` (array, optional, min:1)
- `abilities` (array, optional, min:1)

**Przykład:**
```bash
curl -X PUT \
  -H "Content-Type: application/json" \
  -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  -d '{
    "height": 25,
    "weight": 1200
  }' \
  http://localhost:8000/api/pokemon/custom-pokemon/superpokemon
```

**Odpowiedź 200:**
```json
{
  "message": "Custom pokemon updated successfully",
  "pokemon": {
    "name": "superpokemon",
    "height": 25,
    "weight": 1200,
    "types": ["fire", "electric"],
    "abilities": ["super-power", "mega-strike"],
    "is_custom": true
  }
}
```

**Kody błędów:**
- `404` - Pokemon nie znaleziony
- `422` - Błąd walidacji
- `401` - Brak autoryzacji

---

#### DELETE /api/pokemon/custom-pokemon/{name}
Usuwa własnego Pokémona.

**Parametry URL:**
- `name` (string) - nazwa Pokémona do usunięcia

**Przykład:**
```bash
curl -X DELETE \
  -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  http://localhost:8000/api/pokemon/custom-pokemon/superpokemon
```

**Odpowiedź 200:**
```json
{
  "message": "Custom pokemon deleted successfully"
}
```

**Kody błędów:**
- `404` - Pokemon nie znaleziony
- `401` - Brak autoryzacji

---

## Cache Management (Etap 5)

API wykorzystuje Redis do cachowania odpowiedzi z PokeAPI, co znacząco przyspiesza działanie i redukuje obciążenie zewnętrznego API.

### Endpointy zarządzania cache

Wszystkie endpointy wymagają nagłówka `X-SUPER-SECRET-KEY`.

#### GET /api/pokemon/cache/stats
Zwraca statystyki cache - ile pokemonów jest cachowanych i kiedy wygasają.

**Przykład:**
```bash
curl -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  http://localhost:8000/api/pokemon/cache/stats
```

**Odpowiedź 200:**
```json
{
  "total_cached": 2,
  "cached_pokemons": [
    {
      "pokemon": "pikachu",
      "ttl_seconds": 43200,
      "expires_in": "12:00:00"
    },
    {
      "pokemon": "charizard",
      "ttl_seconds": 43195,
      "expires_in": "11:59:55"
    }
  ]
}
```

#### DELETE /api/pokemon/cache/clear/{name}
Czyści cache dla konkretnego pokemona.

**Przykład:**
```bash
curl -X DELETE \
  -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  http://localhost:8000/api/pokemon/cache/clear/pikachu
```

**Odpowiedź 200:**
```json
{
  "message": "Cache cleared successfully",
  "pokemon": "pikachu",
  "key": "pokemon:api:pikachu"
}
```

#### DELETE /api/pokemon/cache/clear
Czyści cały cache pokemonów.

**Przykład:**
```bash
curl -X DELETE \
  -H "X-SUPER-SECRET-KEY: tajne-haslo-123" \
  http://localhost:8000/api/pokemon/cache/clear
```

**Odpowiedź 200:**
```json
{
  "message": "All pokemon cache cleared",
  "cleared_count": 5
}
```

### Szczegóły działania cache

**TTL (Time To Live):**
- Cache jest ważny do następnego dnia o 12:00 UTC+1 (Europe/Warsaw)
- Jeśli teraz jest przed 12:00 → cache do dzisiaj 12:00
- Jeśli teraz jest po 12:00 → cache do jutra 12:00

**Co jest cachowane:**
- ✅ Dane z PokeAPI (zewnętrzne API)
- ❌ Custom pokemony (zawsze świeże z bazy danych)
- ❌ Lista zakazanych (zawsze świeża z bazy danych)

**Backend:**
- Redis (w Dockerze)
- Klucze: `pokemon_cache:pokemon:api:{nazwa}`

---

## Zaimplementowane funkcjonalności

- **Etap 1**: Rejestr Pokemonów zakazanych (CRUD)
- **Etap 2**: Autoryzacja przez middleware (X-SUPER-SECRET-KEY)
- **Etap 3**: Pobieranie informacji o Pokemonach z PokeAPI
- **Etap 4**: Własne (custom) Pokemony (CRUD + integracja z /pokemon/info)
- **Etap 5**: Cache Redis z TTL do 12:00 UTC+1

## Architektura

Projekt wykorzystuje standardową architekturę Laravel z dodatkową warstwą Service:

```
Controllers → Services → Models → Database
```

### Przepływ danych:

1. **Request** → walidacja przez Form Request
2. **Controller** → odbiera zwalidowane dane
3. **Service Layer** → logika biznesowa (fetch z PokeAPI, filtrowanie, cache)
4. **Cache (Redis)** → przechowuje dane z PokeAPI
5. **Model/Eloquent** → operacje na bazie danych
6. **Response** → zwraca JSON

### Struktura katalogów:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Pokemon/
│   │       ├── PokemonBannedController.php    # Zarządzanie listą zakazanych
│   │       ├── PokemonController.php          # Pobieranie info o pokemonach
│   │       ├── PokemonCustomController.php    # CRUD dla własnych pokemonów
│   │       └── CacheController.php            # Zarządzanie cache
│   ├── Middleware/
│   │   ├── CheckSecretKey.php                 # Autoryzacja przez X-SUPER-SECRET-KEY
│   │   └── ForceJsonResponse.php              # Wymuszanie JSON odpowiedzi
│   └── Requests/
│       └── Pokemon/
│           ├── StorePokemonBannedRequest.php
│           ├── GetPokemonInfoRequest.php
│           ├── StorePokemonCustomRequest.php
│           └── UpdatePokemonCustomRequest.php
├── Models/
│   └── Pokemon/
│       ├── PokemonBanned.php
│       └── PokemonCustom.php
└── Services/
    ├── PokemonService.php              # Główny serwis (PokeAPI + cache)
    ├── PokemonBannedService.php        # Serwis dla banned pokemonów
    └── PokemonCustomService.php        # Serwis dla custom pokemonów
```

Do utworzenia dokumentacji wykorzystano: Claude Code
