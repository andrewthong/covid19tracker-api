<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

use App\RapidTest;
use App\PostalDistrict;
use DateTime;

class RapidTestTest extends TestCase
{

    /**
     * setting up the test
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * rapid test submissions
     */
    public function test_submission()
    {
        // construct random submission
        $rt = RapidTest::getTestResultsTypes();
        $p1 = PostalDistrict::inRandomOrder()->first()->letter;
        if(!$p1) $p1 = 'A';
        $payload = [
            'test_date' => date('Y-m-d', strtotime('-1 day')),
            'test_result' => $rt[array_rand($rt)],
            'postal_code' => $p1.rand(0, 9).$p1,
            'age' => '12345',
        ];

        $response = $this->json('POST', '/collect/rapid-test/', $payload);
        $response->assertJson(fn (AssertableJson $obj) =>
            $obj->where('created', true)
        );

        return $payload;
    }

    /**
     * @depends test_submission
     */
    public function test_has_submission($payload)
    {
        // ISO date for MongoDB
        $payload['test_date'] = new DateTime($payload['test_date']);
        $submission = RapidTest::where($payload)->first();
        // set status key so it's not processed
        $submission[RapidTest::reportStatusKey()] = 'ignored-test';
        $submission->save();
        // test
        $arr = $submission->toArray();
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('ip', $arr);
        $this->assertEquals($payload['test_result'],    $arr['test_result']);
        $this->assertEquals($payload['postal_code'],    $arr['postal_code']);
        $this->assertEquals($payload['age'],            $arr['age']);
    }
}
