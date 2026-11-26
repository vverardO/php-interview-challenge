<?php

namespace App\Http\Controllers\DogFacts;

use App\Http\Controllers\Controller;
use App\Http\Requests\DogFacts\IndexRequest;
use App\Http\Resources\DogFactResource;
use App\Models\DogFact;
use Illuminate\Database\QueryException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    private const TIMEOUT_ERROR_CODE = 3024;

    public function __invoke(IndexRequest $request): AnonymousResourceCollection
    {
        $pageSize = (int) $request->validated(
            'pageSize',
            config('pagination.default_size')
        );

        try {
            $dogFacts = $this->search(
                $request,
                $pageSize
            );
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) !== self::TIMEOUT_ERROR_CODE) {
                throw $exception;
            }

            return DogFactResource::collection($this->emptyPage(
                $request,
                $pageSize
            ))->additional([
                'message' => config('dog-facts.default_message'),
            ]);
        }

        return DogFactResource::collection($dogFacts);
    }

    private function search(
        IndexRequest $request,
        int $pageSize
    ): LengthAwarePaginator {
        $timeout = (int) config('dog-facts.index_timeout_ms');

        DB::statement("SET SESSION max_execution_time = {$timeout}");

        try {
            return DogFact::query()
                ->filter($request->validated())
                ->orderBy('id')
                ->paginate($pageSize)
                ->withQueryString();
        } finally {
            DB::statement('SET SESSION max_execution_time = 0');
        }
    }

    private function emptyPage(
        IndexRequest $request,
        int $pageSize
    ): LengthAwarePaginator {
        return new LengthAwarePaginator(
            [],
            0,
            $pageSize,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
