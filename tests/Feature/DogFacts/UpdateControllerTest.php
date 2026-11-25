<?php
declare(strict_types=1);

namespace Tests\Feature\DogFacts;

use App\Models\DogFact;
use Tests\TestCase;

class UpdateControllerTest extends TestCase
{
    protected ?DogFact $dogFact;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dogFact = new DogFact();
        $this->dogFact->fact = 'This is the original v2 dog fact';

        $this->dogFact->save();
    }

    protected function tearDown(): void
    {
        $this->cleanUpModels([$this->dogFact]);

        parent::tearDown();
    }

    public function testSuccess(): void
    {
        $response = $this->putJson("/api/v2/dog-facts/{$this->dogFact->id}", [
            'fact' => 'This is the updated v2 dog fact',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->dogFact->id);
        $response->assertJsonPath('data.fact', 'This is the updated v2 dog fact');
        $this->assertSame('This is the updated v2 dog fact', $this->dogFact->fresh()->fact);
    }

    public function testNotFound(): void
    {
        $response = $this->putJson('/api/v2/dog-facts/6942069', [
            'fact' => 'This is the updated v2 dog fact',
        ]);

        $response->assertStatus(404);
    }

    public function testValidationFailure(): void
    {
        $url = "/api/v2/dog-facts/{$this->dogFact->id}";

        $this->putJson($url, [])->assertStatus(422);
        $this->putJson($url, ['fact' => null])->assertStatus(422);
        $this->putJson($url, ['fact' => 123])->assertStatus(422);
        $this->putJson($url, ['fact' => str_repeat('a', 256)])->assertStatus(422);

        $this->assertSame('This is the original v2 dog fact', $this->dogFact->fresh()->fact);
    }
}
