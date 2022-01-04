<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    /**
     * full health region vaccine report
     */
    public function test_partner_health_region_vaccine_report()
    {
        $response = $this->get('_p/'.env('PARTNER01', 'none').'/report-hr-vaccination');
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('last_updated')
                ->has('data')
                ->has('data.0', fn ($json) =>
                    $json->has('date')
                        ->has('hr_uid')
                        ->has('total_vaccinations')
                        ->has('total_vaccinated')
                        ->has('total_boosters_1')
                )
        );
    }
}
