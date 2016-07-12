<?php

use Illuminate\Http\Request;
use Mockery as m;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;
use Recca0120\LaravelPayum\Service\Payment;

class PaymentControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testCommonBehaviors()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $controller = new PaymentController();
        $payment = m::mock(Payment::class);
        $request = m::mock(Request::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $methods = ['authorize', 'capture', 'notify', 'payout', 'refund', 'sync'];

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        foreach ($methods as $method) {
            $exceptedPayumToken = uniqid();
            $payment->shouldReceive($method)->with($request, $exceptedPayumToken)->andReturn($exceptedPayumToken);
            $this->assertSame($exceptedPayumToken, call_user_func_array([$controller, $method], [$payment, $request, $exceptedPayumToken]));
        }
    }

    public function testNotifyUnsafe()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $controller = new PaymentController();
        $payment = m::mock(Payment::class);
        $request = m::mock(Request::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedGatewayName = 'fooGatewayName';
        $payment->shouldReceive('notifyUnsafe')->with($exceptedGatewayName)->andReturn($exceptedGatewayName);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedGatewayName, $controller->notifyUnsafe($payment, $exceptedGatewayName));
    }
}
