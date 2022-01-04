<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SrReportTest extends TestCase
{
    /**
     * base summary sr vaccine reports
     */
    public function test_summary()
    {
        $response = $this->json('GET', '/reports/sub-regions/summary');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('last_updated')
                ->has('data')
                ->has('data.0', fn ($json) =>
                    $json->has('code')
                        ->has('date')
                        ->has('total_dose_1')
                        ->has('percent_dose_1')
                        ->has('source_dose_1')
                        ->has('total_dose_2')
                        ->has('percent_dose_2')
                        ->has('source_dose_2')
                        ->has('total_dose_3')
                        ->has('percent_dose_3')
                        ->has('source_dose_3')
                )
                ->etc()
        );
    }

    /**
     * base recent sr vaccine reports
     */
    public function test_recent_reports()
    {
        $response = $this->json('GET', '/reports/sub-regions/recent');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('last_updated')
                ->has('recent_from')
                ->has('data')
                ->has('data.'.rand(1,10), fn ($json) =>
                    $json->has('code')
                        ->has('date')
                        ->has('total_dose_1')
                        ->has('percent_dose_1')
                        ->has('source_dose_1')
                        ->has('total_dose_2')
                        ->has('percent_dose_2')
                        ->has('source_dose_2')
                        ->has('total_dose_3')
                        ->has('percent_dose_3')
                        ->has('source_dose_3')
                )
                ->etc()
        );
    }

    /**
     * base sr vaccine report for a specific sub region
     */
    public function test_specific_sub_region_report()
    {
        $response = $this->json('GET', '/reports/sub-regions/ab123');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('sub_region')
                ->has('data')
                ->has('data.0', fn ($json) =>
                    $json->has('date')
                        ->has('total_dose_1')
                        ->has('percent_dose_1')
                        ->has('source_dose_1')
                        ->has('total_dose_2')
                        ->has('percent_dose_2')
                        ->has('source_dose_2')
                        ->has('total_dose_3')
                        ->has('percent_dose_3')
                        ->has('source_dose_3')
                )
                ->etc()
        );
    }

}
