<?php

namespace App\Http\Controllers\DogFacts;

use App\Http\Controllers\Controller;
use App\Http\Requests\DogFacts\SaveRequest;
use App\Http\Resources\DogFactResource;
use App\Models\DogFact;
use Illuminate\Support\Facades\Cache;

class UpdateController extends Controller
{
    public function __invoke(
        SaveRequest $request,
        DogFact $dogFact
    ): DogFactResource {
        $dogFact->update($request->validated());

        Cache::forget(DogFact::cacheKey($dogFact->id));

        return new DogFactResource($dogFact);
    }
}
