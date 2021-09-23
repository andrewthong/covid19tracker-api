<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;


class ReportTest extends TestCase
{

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_reports()
    {
        $response = $this->json('GET', '/reports');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('province')
                ->has('last_updated')
                ->has('data')
                ->has('data.0', fn ($json) =>
                    $json->has('date')
                        ->has('change_cases')
                        ->has('change_fatalities')
                        ->has('change_tests')
                        ->has('total_cases')
                        ->has('total_fatalities')
                        ->has('total_tests')
                        ->etc()
                )
                ->etc()
        );
    }

    public function test_province_reports()
    {
        $response = $this->json('GET', '/reports/province/ab?after=2021-08-01');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('province', 'ab')
                ->has('last_updated')
                ->has('data')
                ->has('data.0', fn ($json) =>
                    $json->where('date', '2021-08-01')
                        ->etc()
                )
                ->etc()
        );
    }
}
