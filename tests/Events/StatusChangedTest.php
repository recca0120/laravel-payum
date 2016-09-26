<?php

use Mockery as m;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetStatusInterface;
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

        $status = m::mock(GetStatusInterface::class);
        $payment = m::mock(PaymentInterface::class);

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
