<?php

use Illuminate\Http\Request;
use Mockery as m;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;
use Recca0120\LaravelPayum\Payment;

class NotifyControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_notify()
    {
        $request = m::mock(Request::class);
        $payumToken = uniqid();
        $payment = m::mock(Payment::class)
            ->shouldReceive('send')->with($request, $payumToken, m::type(Closure::class))->once()->andReturnUsing(function ($request, $payumToken, $closure) {
                $token = m::mock(TokenInterface::class);

                $httpRequestVerifier = m::mock(HttpRequestVerifier::class);

                $gateway = m::mock(GatewayInterface::class)
                    ->shouldReceive('execute')->with(m::type(Notify::class))->once()
                    ->mock();

                return $closure($gateway, $token, $httpRequestVerifier);
            })
            ->mock();
        $controller = new PaymentController();
        $controller->notify($payment, $request, $payumToken);
    }

    public function test_notify_unsafe()
    {
        $gatewayName = 'test';
        $payumToken = uniqid();
        $payment = m::mock(Payment::class)
            ->shouldReceive('getPayum')->once()->andReturnSelf()
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturnSelf()
            ->shouldReceive('execute')->with(m::type(Notify::class))->once()
            ->mock();
        $controller = new PaymentController();
        $controller->notifyUnsafe($payment, $gatewayName);
    }
}
