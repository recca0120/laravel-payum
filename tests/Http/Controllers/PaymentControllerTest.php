<?php

use Mockery as m;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;

class PaymentControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_common_behaviors()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $controller = new PaymentController();
        $payumService = m::mock('Recca0120\LaravelPayum\Service\PayumService');
        $request = m::mock('Illuminate\Http\Request');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $methods = ['authorize', 'capture', 'cancel', 'notify', 'payout', 'refund', 'sync'];

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        foreach ($methods as $method) {
            $exceptedPayumToken = uniqid();
            $payumService->shouldReceive('receive'.ucfirst($method))->with($request, $exceptedPayumToken)->andReturn($exceptedPayumToken);
            $this->assertSame($exceptedPayumToken, call_user_func_array([$controller, $method], [$payumService, $request, $exceptedPayumToken]));
        }
    }

    public function test_notify_unsafe()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $controller = new PaymentController();
        $payumService = m::mock('Recca0120\LaravelPayum\Service\PayumService');
        $request = m::mock('Illuminate\Http\Request');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedGatewayName = 'fooGatewayName';
        $payumService->shouldReceive('receiveNotifyUnsafe')->with($exceptedGatewayName)->andReturn($exceptedGatewayName);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedGatewayName, $controller->notifyUnsafe($payumService, $exceptedGatewayName));
    }
}
