<?php

use Illuminate\Http\Request;
use Mockery as m;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Refund;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;
use Recca0120\LaravelPayum\Payment;

class RefundControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_refund()
    {
        $request = m::mock(Request::class);
        $payumToken = uniqid();
        $payment = m::mock(Payment::class)
            ->shouldReceive('send')->with($request, $payumToken, m::type(Closure::class))->once()->andReturnUsing(function ($request, $payumToken, $closure) {
                $token = m::mock(TokenInterface::class)
                    ->shouldReceive('getAfterUrl')->twice()->andReturn('test')
                    ->mock();

                $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
                    ->shouldReceive('invalidate')->with($token)->once()
                    ->mock();

                $gateway = m::mock(GatewayInterface::class)
                    ->shouldReceive('execute')->with(m::type(Refund::class))->once()
                    ->mock();

                return $closure($gateway, $token, $httpRequestVerifier);
            })
            ->mock();
        $controller = new PaymentController();
        $controller->refund($payment, $request, $payumToken);
    }
}
