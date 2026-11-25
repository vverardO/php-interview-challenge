<?php

namespace App\Http\Controllers\DogFacts;

use App\Http\Controllers\Controller;
use App\Http\Resources\DogFactResource;
use App\Models\DogFact;
use Illuminate\Support\Facades\Cache;

class ShowController extends Controller
{
    public function __invoke(int $id): DogFactResource
    {
        $dogFact = Cache::remember(
            DogFact::cacheKey($id),
            now()->addMinutes((int) config('dog-facts.cache_minutes')),
            fn () => DogFact::find($id)
        );

        if (!$dogFact) {
            abort(404);
        }

        return new DogFactResource($dogFact);
    }
}
