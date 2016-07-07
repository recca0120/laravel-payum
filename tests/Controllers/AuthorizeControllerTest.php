<?php

use Illuminate\Http\Request;
use Mockery as m;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;
use Recca0120\LaravelPayum\Payment;

class AuthorizeControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_authorize()
    {
        $request = m::mock(Request::class);
        $payumToken = uniqid();
        $payment = m::mock(Payment::class)
            ->shouldReceive('send')->with($request, $payumToken, m::type(Closure::class))->once()->andReturnUsing(function ($request, $payumToken, $closure) {
                $token = m::mock(TokenInterface::class)
                    ->shouldReceive('getAfterUrl')->once()->andReturn('test')
                    ->mock();

                $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
                    ->shouldReceive('invalidate')->with($token)->once()
                    ->mock();

                $gateway = m::mock(GatewayInterface::class)
                    ->shouldReceive('execute')->with(m::type(Authorize::class))->once()
                    ->mock();

                return $closure($gateway, $token, $httpRequestVerifier);
            })
            ->mock();
        $controller = new PaymentController();
        $controller->authorize($payment, $request, $payumToken);
    }
}
