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

        $this->repository = new DogFactRepository();
    }

    protected function tearDown(): void
    {
        $this->cleanUpModels($this->dogFacts);

        parent::tearDown();
    }

    public function testGetAllWithPaginationUsesDefaultPageSize(): void
    {
        $paginator = $this->repository->getAllWithPagination();

        $this->assertSame(25, $paginator->perPage());
        $this->assertCount(25, $paginator->items());
        $this->assertStringNotContainsString('pageSize', $paginator->url(2));
    }

    public function testGetAllWithPaginationUsesGivenPageSize(): void
    {
        $paginator = $this->repository->getAllWithPagination(['pageSize' => 10]);

        $this->assertSame(10, $paginator->perPage());
        $this->assertCount(10, $paginator->items());
        $this->assertStringContainsString('pageSize=10', $paginator->url(2));
    }

    public function testGetById(): void
    {
        $dogFact = $this->createDogFact('This is a dog fact to be found');

        $found = $this->repository->getById($dogFact->id);

        $this->assertInstanceOf(DogFact::class, $found);
        $this->assertSame($dogFact->id, $found->id);
        $this->assertSame('This is a dog fact to be found', $found->fact);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $this->assertNull($this->repository->getById(6942069));
    }

    public function testStore(): void
    {
        $dogFact = $this->repository->store(['fact' => 'This is a stored dog fact']);
        $this->dogFacts[] = $dogFact;

        $this->assertInstanceOf(DogFact::class, $dogFact);
        $this->assertSame('This is a stored dog fact', DogFact::find($dogFact->id)->fact);
    }

    public function testUpdate(): void
    {
        $dogFact = $this->createDogFact('This is the original dog fact');

        $updated = $this->repository->update($dogFact, ['fact' => 'This is the updated dog fact']);

        $this->assertSame($dogFact->id, $updated->id);
        $this->assertSame('This is the updated dog fact', DogFact::find($dogFact->id)->fact);
    }

    public function testDestroy(): void
    {
        $dogFact = $this->createDogFact('This is a dog fact that will be deleted');

        $this->assertTrue($this->repository->destroy($dogFact));
        $this->assertNull(DogFact::find($dogFact->id));
    }

    protected function createDogFact(string $fact): DogFact
    {
        $dogFact = new DogFact();
        $dogFact->fact = $fact;
        $dogFact->save();

        $this->dogFacts[] = $dogFact;

        return $dogFact;
    }
}
