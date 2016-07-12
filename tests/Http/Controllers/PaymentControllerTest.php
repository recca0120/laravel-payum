<?php

use Illuminate\Http\Request;
use Mockery as m;
use Recca0120\LaravelPayum\Http\Controllers\Behavior\DoneBehavior;
use Recca0120\LaravelPayum\Http\Controllers\Behavior\PrepareBehavior;
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

    public function testPrepare()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $controller = new PaymentController2();
        $payment = m::mock(Payment::class);
        $request = m::mock(Request::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $payment->shouldReceive('prepare')->with('offline', m::type(Closure::class));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $controller->prepare($payment);
    }

    public function testDone()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $controller = new PaymentController2();
        $payment = m::mock(Payment::class);
        $request = m::mock(Request::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedPayumToken = uniqid();
        $payment->shouldReceive('done')->with($request, $exceptedPayumToken, m::type(Closure::class));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $controller->done($payment, $request, $exceptedPayumToken);
    }
}

class PaymentController2 extends PaymentController
{
    use PrepareBehavior,
        DoneBehavior;

    protected $gatewayName = 'offline';

    public function onPrepare($request, $payment, $gatewayName, $storage, $payum)
    {
    }

    public function onDone($status, $payment, $gateway, $token)
    {
    }
}
