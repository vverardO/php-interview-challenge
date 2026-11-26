<?php

namespace App\Http\Controllers\DogFacts;

use App\Http\Controllers\Controller;
use App\Models\DogFact;
use Illuminate\Http\JsonResponse;

class MetadataController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $filters = collect(DogFact::FILTERS)
            ->map(fn (
                array $filter,
                string $name
            ) => ['name' => $name] + $filter)
            ->values();

        return response()->json([
            'filters' => $filters,
            'pagination' => [
                'page' => [
                    'type' => 'integer',
                    'default' => 1,
                    'min' => 1,
                ],
                'pageSize' => [
                    'type' => 'integer',
                    'default' => (int) config('pagination.default_size'),
                    'min' => 1,
                    'max' => (int) config('pagination.max_size'),
                ],
            ],
        ]);
    }
}
