<?php

declare(strict_types=1);

namespace Tests\Feature\DogFacts;

use App\Models\DogFact;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected string $fact = 'This is a test fact from the v2 StoreControllerTest';

    public function test_success(): void
    {
        $response = $this->postJson('/api/v2/dog-facts', [
            'fact' => $this->fact,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.fact', $this->fact);
        $this->assertSame($this->fact, DogFact::find($response->json('data.id'))->fact);

        DogFact::destroy($response->json('data.id'));
    }

    public function test_validation_failure(): void
    {
        $this->postJson('/api/v2/dog-facts', [])->assertStatus(422);
        $this->postJson('/api/v2/dog-facts', ['fact' => null])->assertStatus(422);
        $this->postJson('/api/v2/dog-facts', ['fact' => 123])->assertStatus(422);
        $this->postJson('/api/v2/dog-facts', ['fact' => str_repeat('a', 256)])->assertStatus(422);
    }
}
