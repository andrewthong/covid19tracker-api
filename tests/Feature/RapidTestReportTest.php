<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

use App\RapidTestReport;

class RapidTestReportTest extends TestCase
{
    /**
     * summary
     */
    public function test_summary()
    {
        $response = $this->json('GET', '/rapid-tests/');
        $response->assertJson(fn (AssertableJson $obj) =>
            $obj->has('last_updated')
                ->has('data', 1)
                ->has('data.0', fn ($obj) =>
                    $obj->has('latest_date')
                        ->has('earliest_date')
                        ->has('total_positive')
                        ->has('total_negative')
                        ->has('total_invalid')
                )
                ->etc()
        );
    }

    /**
     * split summary
     */
    public function test_summary_split()
    {
        $response = $this->json('GET', '/rapid-tests/split');
        $response->assertJson(fn (AssertableJson $obj) =>
        $obj->has('last_updated')
                ->has('data')
                ->has('data.0', fn ($obj) =>
                    $obj->has('province')
                        ->has('latest_date')
                        ->has('earliest_date')
                        ->has('total_positive')
                        ->has('total_negative')
                        ->has('total_invalid')
                )
                ->etc()
        );
    }

    /**
     * reports
     */
    public function test_reports()
    {
        $response = $this->json('GET', '/rapid-tests/report');
        $response->assertJson(fn (AssertableJson $obj) =>
            $obj->has('province')
                ->has('last_updated')
                ->has('data')
                ->has('data.0', fn ($obj) =>
                    $obj->has('date')
                        ->has('positive')
                        ->has('negative')
                        ->has('invalid')
                )
        );
    }

    /**
     * reports by province
     */
    public function test_reports_by_province()
    {
        $province = RapidTestReport::inRandomOrder()->first()->province;
        $response = $this->json('GET', '/rapid-tests/report/province/'.$province);
        $response->assertJson(fn (AssertableJson $obj) =>
            $obj->where('province', $province)
                ->has('last_updated')
                ->has('data')
                ->has('data.0', fn ($obj) =>
                    $obj->has('date')
                        ->has('positive')
                        ->has('negative')
                        ->has('invalid')
                )
        );
    }
}
