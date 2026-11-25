<?php

namespace App\Http\Controllers\DogFacts;

use App\Http\Controllers\Controller;
use App\Models\DogFact;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class DestroyController extends Controller
{
    public function __invoke(DogFact $dogFact): Response
    {
        $dogFact->delete();

        Cache::forget(DogFact::cacheKey($dogFact->id));

        return response()->noContent();
    }
}
