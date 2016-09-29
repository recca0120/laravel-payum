<?php

use Mockery as m;
use Recca0120\LaravelPayum\Events\StatusChanged;

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

        $status = m::mock('Payum\Core\Request\GetStatusInterface');
        $payment = m::mock('Payum\Core\Model\PaymentInterface');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $event = new StatusChanged($status, $payment);
        $this->assertSame($status, $event->status);
        $this->assertSame($payment, $event->payment);
    }
}
