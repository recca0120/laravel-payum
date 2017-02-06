<?php

namespace Recca0120\LaravelPayum\Tests\Http\Controllers;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;

class PaymentControllerTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function test_receive_authorize()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumService = m::spy('Recca0120\LaravelPayum\Service\PayumService');
        $token = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService
            ->shouldReceive('receiveAuthorize')->with($token)->andReturn($token);

        $controller = new PaymentController();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $controller->receiveAuthorize($payumService, $token);
        $payumService->shouldHaveReceived('receiveAuthorize')->with($token)->once();
    }

    public function test_receive_capture()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumService = m::spy('Recca0120\LaravelPayum\Service\PayumService');
        $token = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService
            ->shouldReceive('receiveCapture')->with($token)->andReturn($token);

        $controller = new PaymentController();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $controller->receiveCapture($payumService, $token);
        $payumService->shouldHaveReceived('receiveCapture')->with($token)->once();
    }

    public function test_receive_cancel()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumService = m::spy('Recca0120\LaravelPayum\Service\PayumService');
        $token = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService
            ->shouldReceive('receiveCancel')->with($token)->andReturn($token);

        $controller = new PaymentController();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $controller->receiveCancel($payumService, $token);
        $payumService->shouldHaveReceived('receiveCancel')->with($token)->once();
    }

    public function test_receive_notify()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumService = m::spy('Recca0120\LaravelPayum\Service\PayumService');
        $token = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService
            ->shouldReceive('receiveNotify')->with($token)->andReturn($token);

        $controller = new PaymentController();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $controller->receiveNotify($payumService, $token);
        $payumService->shouldHaveReceived('receiveNotify')->with($token)->once();
    }

    public function test_receive_unsafe_notify()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumService = m::spy('Recca0120\LaravelPayum\Service\PayumService');
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService
            ->shouldReceive('receiveNotifyUnsafe')->with($gatewayName)->andReturn($gatewayName);

        $controller = new PaymentController();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $controller->receiveNotifyUnsafe($payumService, $gatewayName);
        $payumService->shouldHaveReceived('receiveNotifyUnsafe')->with($gatewayName)->once();
    }

    public function test_receive_payout()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumService = m::spy('Recca0120\LaravelPayum\Service\PayumService');
        $token = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService
            ->shouldReceive('receivePayout')->with($token)->andReturn($token);

        $controller = new PaymentController();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $controller->receivePayout($payumService, $token);
        $payumService->shouldHaveReceived('receivePayout')->with($token)->once();
    }

    public function test_receive_refund()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumService = m::spy('Recca0120\LaravelPayum\Service\PayumService');
        $token = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService
            ->shouldReceive('receiveRefund')->with($token)->andReturn($token);

        $controller = new PaymentController();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $controller->receiveRefund($payumService, $token);
        $payumService->shouldHaveReceived('receiveRefund')->with($token)->once();
    }

    public function test_receive_sync()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumService = m::spy('Recca0120\LaravelPayum\Service\PayumService');
        $token = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService
            ->shouldReceive('receiveSync')->with($token)->andReturn($token);

        $controller = new PaymentController();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $controller->receiveSync($payumService, $token);
        $payumService->shouldHaveReceived('receiveSync')->with($token)->once();
    }
}
