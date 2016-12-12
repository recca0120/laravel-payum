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
        | Arrange
        |------------------------------------------------------------
        */

        $status = m::spy('Payum\Core\Request\GetStatusInterface');
        $payment = m::spy('Payum\Core\Model\PaymentInterface');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $event = new StatusChanged($status, $payment);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($status, $event->status);
        $this->assertSame($payment, $event->payment);
    }
}
