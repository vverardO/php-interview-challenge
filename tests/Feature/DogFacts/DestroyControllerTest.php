<?php
declare(strict_types=1);

namespace Tests\Feature\DogFacts;

use App\Models\DogFact;
use Tests\TestCase;

class DestroyControllerTest extends TestCase
{
    public function testSuccess(): void
    {
        $dogFact = new DogFact();
        $dogFact->fact = 'This is a v2 dog fact that will be deleted';
        $dogFact->save();

        $response = $this->deleteJson("/api/v2/dog-facts/{$dogFact->id}");

        $response->assertStatus(204);
        $this->assertNull(DogFact::find($dogFact->id));
    }

    public function testNotFound(): void
    {
        $response = $this->deleteJson('/api/v2/dog-facts/6942069');

        $response->assertStatus(404);
    }
}
