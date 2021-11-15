<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LocationTest extends TestCase
{
    /**
     * basic provinces
     */
    public function test_provinces()
    {
        $response = $this->get('/provinces/');
        $response->assertStatus(200);
    }

    /**
     * specific health region
     */
    public function test_single_province()
    {
        $response = $this->get('/province/ab');
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('0.id')
                ->where('0.code', 'AB')
                ->has('0.name')
                ->has('0.data_source')
                ->has('0.population')
                ->has('0.area')
                ->has('0.gdp')
                ->has('0.geographic')
                ->has('0.data_status')
                ->has('0.created_at')
                ->has('0.updated_at')
                ->has('0.density')
        );
    }

    /**
     * basic health regions
     */
    public function test_health_regions()
    {
        $response = $this->get('/regions/');
        $response->assertStatus(200);
    }

    /**
     * specific health region
     */
    public function test_single_health_region()
    {
        $response = $this->get('/regions/1201');
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data')
                ->where('data.hr_uid', 1201)
                ->has('data.province')
                ->has('data.engname')
                ->has('data.frename')
        );
    }

    /**
     * basic sub regions
     */
    public function test_sub_regions()
    {
        $response = $this->get('/sub-regions/');
        $response->assertStatus(200);
    }

    /**
     * specific sub region
     */
    public function test_single_sub_region()
    {
        $response = $this->get('/sub-regions/ab012');
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data')
                ->where('data.code', 'AB012')
                ->has('data.province')
                ->has('data.zone')
                ->has('data.region')
                ->has('data.population')
        );
    }
}