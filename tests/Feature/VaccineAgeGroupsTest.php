<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AgeGroupsTest extends TestCase
{
    /**
     * vaccine age groups
     */
    public function test_vaccine_age_groups()
    {
        $response = $this->json('GET', '/vaccines/age-groups');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('province')
                ->has('data')
                ->has('data.0', fn ($json) =>
                    $json->has('date')
                        ->has('data')
                )
        );
    }

    /**
     * split vaccine age groups
     */
    public function test_vaccine_age_groups_split()
    {
        $response = $this->json('GET', '/vaccines/age-groups/split');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('province', 'All')
                ->has('data')
                ->has('data.0', fn ($json) =>
                    $json->has('date')
                        ->has('data')
                        ->has('province')
                )
        );
    }

    /**
     * vaccine age groups by province
     */
    public function test_vaccine_age_groups_by_province()
    {
        $response = $this->json('GET', '/vaccines/age-groups/province/ab');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('province', 'ab')
                ->has('data')
                ->has('data.0', fn ($json) =>
                    $json->has('date')
                        ->has('data')
                )
        );
    }

}
