<?php

declare(strict_types=1);

namespace Tests\Feature\DogFacts;

use Tests\TestCase;

class MetadataControllerTest extends TestCase
{
    public function test_success(): void
    {
        $response = $this->getJson('/api/v2/dog-facts/metadata');

        $response->assertStatus(200);
        $response->assertExactJson([
            'filters' => [
                [
                    'name' => 'fact',
                    'type' => 'string',
                    'description' => 'Part of the fact text',
                ],
                [
                    'name' => 'createdFrom',
                    'type' => 'date',
                    'format' => 'Y-m-d',
                    'description' => 'Facts created on or after this date',
                ],
                [
                    'name' => 'createdUntil',
                    'type' => 'date',
                    'format' => 'Y-m-d',
                    'description' => 'Facts created on or before this date',
                ],
            ],
            'pagination' => [
                'page' => [
                    'type' => 'integer',
                    'default' => 1,
                    'min' => 1,
                ],
                'pageSize' => [
                    'type' => 'integer',
                    'default' => 25,
                    'min' => 1,
                    'max' => 100,
                ],
            ],
        ]);
    }

    public function test_every_filter_is_accepted(): void
    {
        $response = $this->getJson('/api/v2/dog-facts?fact=dog&createdFrom=2001-01-01&createdUntil=2099-12-31&page=1&pageSize=10');

        $response->assertStatus(200);
    }
}
