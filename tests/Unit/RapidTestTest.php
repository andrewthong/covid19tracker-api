<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use App\RapidTest;

class RapidTestTest extends TestCase
{
    /**
     * result types
     */
    public function test_result_types()
    {
        $this->assertEquals(
            ['positive', 'negative', 'invalid result'],
            RapidTest::getTestResultsTypes()
        );
    }

    /**
     * today should be considered valid
     */
    public function test_today_is_valid()
    {
        $this->assertFalse( RapidTest::isTestDateInvalid( date('Y-m-d') ) );
    }

    /**
     * today should be considered valid
     */
    public function test_day_before_rapid_test_start_date_is_invalid()
    {
        // earliest test date supported
        $min_u = strtotime( env('RAPID_TESTS_START_DATE', '2021-12-01') );
        $day_before_min_u = $min_u - (24 * 60 * 60);
        $res = RapidTest::isTestDateInvalid( date('Y-m-d', $day_before_min_u) );
        $this->assertStringContainsString( 'before', $res );
    }

    /**
     * status key is fillable
     */
    public function test_status_key_is_fillable()
    {
        $rapid_test = new RapidTest();
        $this->assertContains( RapidTest::reportStatusKey(), $rapid_test->getFillable() );
    }
    
}
