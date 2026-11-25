<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DogFact extends Model
{
    use HasFactory;

    public const FILTERS = [
        'fact' => [
            'type' => 'string',
            'description' => 'Part of the fact text',
        ],
        'createdFrom' => [
            'type' => 'date',
            'format' => 'Y-m-d',
            'description' => 'Facts created on or after this date',
        ],
        'createdUntil' => [
            'type' => 'date',
            'format' => 'Y-m-d',
            'description' => 'Facts created on or before this date',
        ],
    ];

    protected $fillable = [
        'fact',
    ];

    public static function cacheKey(int $id): string
    {
        return "dog-facts.{$id}";
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['fact'] ?? null, fn (Builder $query, string $fact) => $query->where('fact', 'like', "%{$fact}%"))
            ->when($filters['createdFrom'] ?? null, fn (Builder $query, string $createdFrom) => $query->whereDate('created_at', '>=', $createdFrom))
            ->when($filters['createdUntil'] ?? null, fn (Builder $query, string $createdUntil) => $query->whereDate('created_at', '<=', $createdUntil));
    }
}
