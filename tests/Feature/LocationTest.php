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
     * basic health regions
     */
    public function test_regions()
    {
        $response = $this->get('/regions/');
        $response->assertStatus(200);
    }
}
