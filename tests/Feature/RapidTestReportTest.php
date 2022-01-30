<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RapidTestReportTest extends TestCase
{
    /**
     * summary
     */
    public function test_summary()
    {
        $response = $this->json('GET', '/rapid-tests/');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('last_updated')
                ->has('data', 1)
                ->has('data.0', fn ($json) =>
                    $json->has('latest_date')
                        ->has('earliest_date')
                        ->has('total_positive')
                        ->has('total_negative')
                        ->has('total_invalid')
                )
                ->etc()
        );
    }
}
