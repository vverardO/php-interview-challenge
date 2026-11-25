<?php
declare(strict_types=1);

namespace Tests\Feature\DogFacts;

use App\Models\DogFact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected array $dogFacts = [];

    protected function tearDown(): void
    {
        $this->cleanUpModels($this->dogFacts);

        parent::tearDown();
    }

    public function testSuccess(): void
    {
        $response = $this->getJson('/api/v2/dog-facts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'fact', 'created_at', 'updated_at'],
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }

    public function testDefaultPageSize(): void
    {
        $response = $this->getJson('/api/v2/dog-facts');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.per_page', 25);
        $response->assertJsonCount(25, 'data');
    }

    public function testSearchUsesTwoSecondTimeout(): void
    {
        $statements = [];

        DB::listen(function (QueryExecuted $query) use (&$statements) {
            $statements[] = $query->sql;
        });

        $this->getJson('/api/v2/dog-facts')->assertStatus(200);

        $this->assertSame('SET SESSION max_execution_time = 2000', $statements[0]);
        $this->assertSame('SET SESSION max_execution_time = 0', end($statements));
    }

    public function testDefaultWhenSearchTimesOut(): void
    {
        config(['dog-facts.index_timeout_ms' => 100]);

        DogFact::addGlobalScope('slow', fn (Builder $query) => $query->whereRaw('SLEEP(1) = 0'));

        $startedAt = microtime(true);
        $response = $this->getJson('/api/v2/dog-facts?pageSize=10&fact=dog');
        $elapsedSeconds = microtime(true) - $startedAt;

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
        $response->assertJsonPath('meta.total', 0);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('message', 'Data is still being processed, please try again shortly');
        $this->assertStringContainsString('fact=dog', $response->json('links.first'));
        $this->assertLessThan(1, $elapsedSeconds);
    }

    public function testTimeoutIsResetAfterSearchTimesOut(): void
    {
        config(['dog-facts.index_timeout_ms' => 100]);

        DogFact::addGlobalScope('slow', fn (Builder $query) => $query->whereRaw('SLEEP(1) = 0'));

        $this->getJson('/api/v2/dog-facts')->assertStatus(200);

        $this->assertSame(0, (int) DB::selectOne('SELECT @@SESSION.max_execution_time AS timeout')->timeout);
    }

    public function testNoDefaultWhenSearchIsFast(): void
    {
        $response = $this->getJson('/api/v2/dog-facts');

        $response->assertStatus(200);
        $response->assertJsonCount(25, 'data');
        $this->assertNull($response->json('message'));
    }

    public function testNoDefaultWhenNothingMatches(): void
    {
        $response = $this->getJson('/api/v2/dog-facts?' . http_build_query(['fact' => 'Missing fact ' . uniqid()]));

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
        $this->assertNull($response->json('message'));
    }

    public function testSuccessWithPage(): void
    {
        $response = $this->getJson('/api/v2/dog-facts?page=2');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.current_page', 2);
    }

    public function testSuccessWithPageSize(): void
    {
        $response = $this->getJson('/api/v2/dog-facts?pageSize=15');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.per_page', 15);
        $response->assertJsonCount(15, 'data');
    }

    public function testInvalidPageSize(): void
    {
        $this->getJson('/api/v2/dog-facts?pageSize=0')->assertStatus(422);
        $this->getJson('/api/v2/dog-facts?pageSize=101')->assertStatus(422);
        $this->getJson('/api/v2/dog-facts?pageSize=abc')->assertStatus(422);
    }

    public function testFilterByFact(): void
    {
        $fact = 'Unique v2 index filter fact ' . uniqid();
        $dogFact = $this->createDogFact($fact);

        $response = $this->getJson('/api/v2/dog-facts?' . http_build_query(['fact' => $fact]));

        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.id', $dogFact->id);
    }

    public function testFilterByCreatedDates(): void
    {
        $dogFact = $this->createDogFact('This is an old dog fact', '2001-01-01 10:00:00');

        $response = $this->getJson('/api/v2/dog-facts?createdFrom=2001-01-01&createdUntil=2001-01-01');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.id', $dogFact->id);
    }

    public function testInvalidDateFilters(): void
    {
        $this->getJson('/api/v2/dog-facts?createdFrom=01/01/2001')->assertStatus(422);
        $this->getJson('/api/v2/dog-facts?createdFrom=2001-01-02&createdUntil=2001-01-01')->assertStatus(422);
    }

    public function testLinksKeepFiltersAndPageSize(): void
    {
        $response = $this->getJson('/api/v2/dog-facts?fact=dog&pageSize=5');

        $response->assertStatus(200);
        $this->assertStringContainsString('fact=dog', $response->json('links.next'));
        $this->assertStringContainsString('pageSize=5', $response->json('links.next'));
    }

    protected function createDogFact(string $fact, ?string $createdAt = null): DogFact
    {
        $dogFact = new DogFact();
        $dogFact->fact = $fact;

        if ($createdAt) {
            $dogFact->created_at = $createdAt;
        }

        $dogFact->save();

        $this->dogFacts[] = $dogFact;

        return $dogFact;
    }
}
