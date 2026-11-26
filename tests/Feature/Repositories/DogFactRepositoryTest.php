<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\DogFact;
use App\Repositories\DogFactRepository;
use Tests\TestCase;

class DogFactRepositoryTest extends TestCase
{
    protected DogFactRepository $repository;

    protected array $dogFacts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DogFactRepository;
    }

    protected function tearDown(): void
    {
        $this->cleanUpModels($this->dogFacts);

        parent::tearDown();
    }

    public function test_get_all_with_pagination_uses_default_page_size(): void
    {
        $paginator = $this->repository->getAllWithPagination();

        $this->assertSame(25, $paginator->perPage());
        $this->assertCount(25, $paginator->items());
        $this->assertStringNotContainsString('pageSize', $paginator->url(2));
    }

    public function test_get_all_with_pagination_uses_given_page_size(): void
    {
        $paginator = $this->repository->getAllWithPagination(['pageSize' => 10]);

        $this->assertSame(10, $paginator->perPage());
        $this->assertCount(10, $paginator->items());
        $this->assertStringContainsString('pageSize=10', $paginator->url(2));
    }

    public function test_get_by_id(): void
    {
        $dogFact = $this->createDogFact('This is a dog fact to be found');

        $found = $this->repository->getById($dogFact->id);

        $this->assertInstanceOf(DogFact::class, $found);
        $this->assertSame($dogFact->id, $found->id);
        $this->assertSame('This is a dog fact to be found', $found->fact);
    }

    public function test_get_by_id_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->getById(6942069));
    }

    public function test_store(): void
    {
        $dogFact = $this->repository->store(['fact' => 'This is a stored dog fact']);
        $this->dogFacts[] = $dogFact;

        $this->assertInstanceOf(DogFact::class, $dogFact);
        $this->assertSame('This is a stored dog fact', DogFact::find($dogFact->id)->fact);
    }

    public function test_update(): void
    {
        $dogFact = $this->createDogFact('This is the original dog fact');

        $updated = $this->repository->update($dogFact, ['fact' => 'This is the updated dog fact']);

        $this->assertSame($dogFact->id, $updated->id);
        $this->assertSame('This is the updated dog fact', DogFact::find($dogFact->id)->fact);
    }

    public function test_destroy(): void
    {
        $dogFact = $this->createDogFact('This is a dog fact that will be deleted');

        $this->assertTrue($this->repository->destroy($dogFact));
        $this->assertNull(DogFact::find($dogFact->id));
    }

    protected function createDogFact(string $fact): DogFact
    {
        $dogFact = new DogFact;
        $dogFact->fact = $fact;
        $dogFact->save();

        $this->dogFacts[] = $dogFact;

        return $dogFact;
    }
}
