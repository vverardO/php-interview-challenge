<?php

namespace App\Http\Controllers\DogFacts;

use App\Http\Controllers\Controller;
use App\Http\Requests\DogFacts\SaveRequest;
use App\Http\Resources\DogFactResource;
use App\Models\DogFact;

class StoreController extends Controller
{
    public function __invoke(SaveRequest $request): DogFactResource
    {
        $dogFact = DogFact::create($request->validated());

        return new DogFactResource($dogFact);
    }
}
