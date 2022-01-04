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
     * summary
     */
    public function test_summary()
    {
        $response = $this->json('GET', '/summary/');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('last_updated')
                ->has('data', 1)
                ->has('data.0', fn ($json) =>
                    $json->has('latest_date')
                        ->has('change_cases')
                        ->has('change_fatalities')
                        ->has('change_tests')
                        ->has('change_hospitalizations')
                        ->has('change_criticals')
                        ->has('change_recoveries')
                        ->has('change_vaccinations')
                        ->has('change_vaccinated')
                        ->has('change_vaccines_distributed')
                        ->has('change_boosters_1')
                        ->has('total_cases')
                        ->has('total_fatalities')
                        ->has('total_tests')
                        ->has('total_hospitalizations')
                        ->has('total_criticals')
                        ->has('total_recoveries')
                        ->has('total_vaccinations')
                        ->has('total_vaccinated')
                        ->has('total_vaccines_distributed')
                        ->has('total_boosters_1')
                )
                ->etc()
        );
    }

    /**
     * base reports
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

    /**
     * reports for only one province
     */
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

    /**
     * reports for only one province and one date
     */
    public function test_province_single_date_report()
    {
        $response = $this->json('GET', '/reports/province/ab?date=2021-01-15');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('province', 'ab')
                ->has('last_updated')
                ->has('data', 1)
                ->has('data.0', fn ($json) =>
                    $json->where('date', '2021-01-15')
                        ->etc()
                )
                ->etc()
        );
    }

    /**
     * reports for one province, one date and one pair of stat
     * boosters_1 vaccine report check
     */
    public function test_province_single_date_stat_report()
    {
        $response = $this->json('GET', '/reports/province/ab?date=2021-09-21&stat=boosters_1');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('province', 'ab')
                ->has('last_updated')
                ->has('data', 1)
                ->has('data.0', fn ($json) =>
                    $json->where('date', '2021-09-21')
                        ->has('change_boosters_1')
                        ->has('total_boosters_1')
                )
                ->etc()
        );
    }

    /**
     * report data range
     */
    public function test_province_date_range()
    {
        $response = $this->json('GET', '/reports/province/ns?after=2021-05-01&before=2021-05-05');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('province', 'ns')
                ->has('last_updated')
                ->has('data', 5)
                ->has('data.0', fn ($json) =>
                    $json->where('date', '2021-05-01')
                        ->etc()
                )
                ->etc()
        );
    }

}