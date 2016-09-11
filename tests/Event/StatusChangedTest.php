<?php

use Mockery as m;
use Recca0120\LaravelPayum\Event\StatusChanged;
use Payum\Core\Request\GetStatusInterface;

class StatusChangedTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_event()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $getStatus = m::mock(GetStatusInterface::class);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $event = new StatusChanged($getStatus);
        $this->assertSame($getStatus, $event->status);
    }
}
