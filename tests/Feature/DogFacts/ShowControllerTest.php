<?php
declare(strict_types=1);

namespace Tests\Feature\DogFacts;

use App\Models\DogFact;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected string $fact = 'This is a v2 dog fact';

    protected ?DogFact $dogFact;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dogFact = new DogFact();
        $this->dogFact->fact = $this->fact;

        $this->dogFact->save();
    }

    protected function tearDown(): void
    {
        if ($this->dogFact->exists) {
            $this->cleanUpModels([$this->dogFact]);
        }

        parent::tearDown();
    }

    public function testSuccess(): void
    {
        $response = $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->dogFact->id);
        $response->assertJsonPath('data.fact', $this->fact);
    }

    public function testNotFound(): void
    {
        $response = $this->getJson('/api/v2/dog-facts/6942069');

        $response->assertStatus(404);
        $this->assertNull($response->json('data'));
    }

    public function testNotFoundIsNotCached(): void
    {
        $this->getJson('/api/v2/dog-facts/6942069');

        $this->assertFalse(Cache::has(DogFact::cacheKey(6942069)));
    }

    public function testFirstLookupIsCached(): void
    {
        $this->assertFalse(Cache::has(DogFact::cacheKey($this->dogFact->id)));

        $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}");

        $this->assertTrue(Cache::has(DogFact::cacheKey($this->dogFact->id)));

        DogFact::whereKey($this->dogFact->id)->update(['fact' => 'Changed without model events']);

        $response = $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}");

        $response->assertJsonPath('data.fact', $this->fact);
    }

    public function testCacheExpiresAfterConfiguredMinutes(): void
    {
        $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}");

        DogFact::whereKey($this->dogFact->id)->update(['fact' => 'Changed without model events']);

        $this->travel(config('dog-facts.cache_minutes') - 1)->minutes();
        $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}")
            ->assertJsonPath('data.fact', $this->fact);

        $this->travel(2)->minutes();
        $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}")
            ->assertJsonPath('data.fact', 'Changed without model events');
    }

    public function testUpdateClearsCache(): void
    {
        $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}");

        $this->putJson("/api/v2/dog-facts/{$this->dogFact->id}", [
            'fact' => 'This is the updated v2 dog fact',
        ])->assertStatus(200);

        $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}")
            ->assertJsonPath('data.fact', 'This is the updated v2 dog fact');
    }

    public function testDestroyClearsCache(): void
    {
        $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}");

        $this->deleteJson("/api/v2/dog-facts/{$this->dogFact->id}")->assertStatus(204);

        $this->getJson("/api/v2/dog-facts/{$this->dogFact->id}")
            ->assertStatus(404);
    }
}
