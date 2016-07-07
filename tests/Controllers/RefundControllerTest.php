<?php

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Mockery as m;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
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
        // $payumToken = uniqid();
        //
        // $token = m::mock(TokenInterface::class)
        //     ->shouldReceive('getGatewayName')->andReturn('test')
        //     ->shouldReceive('getAfterUrl')->andReturn('test')
        //     ->mock();
        //
        // $request = m::mock(Request::class)
        //     ->shouldReceive('merge')->with([
        //         'payum_token' => $payumToken,
        //     ])->once()
        //     ->mock();
        //
        // $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
        //     ->shouldReceive('verify')->with($request)->once()->andReturn($token)
        //     ->shouldReceive('invalidate')->with($token)->once()
        //     ->mock();
        //
        // $gateway = m::mock(GatewayInterface::class)
        //     ->shouldReceive('execute')->with(m::type(Refund::class))->once()
        //     ->mock();
        //
        // $payum = m::mock(Payum::class)
        //     ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
        //     ->shouldReceive('getGateway')->once()->andReturn($gateway)
        //     ->mock();
        //
        // $sessionManager = m::mock(SessionManager::class);
        //
        // $converter = m::mock(ReplyToSymfonyResponseConverter::class);
        //
        // $payment = new Payment($payum, $sessionManager, $converter);
        // $controller = new PaymentController();
        // $controller->refund($payment, $request, $payumToken);
        $request = m::mock(Request::class);
        $payumToken = uniqid();
        $payment = m::mock(Payment::class)
            ->shouldReceive('doAction')->with($request, $payumToken, m::type(Closure::class))->once()->andReturnUsing(function ($request, $payumToken, $closure) {
                $token = m::mock(TokenInterface::class)
                    ->shouldReceive('getAfterUrl')->twice()->andReturn('test')
                    ->mock();

                $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
                    ->shouldReceive('invalidate')->with($token)->once()
                    ->mock();

                $gateway = m::mock(GatewayInterface::class)
                    ->shouldReceive('execute')->with(m::type(Refund::class))->once()
                    ->mock();

                return $closure($httpRequestVerifier, $gateway, $token);
            })
            ->mock();
        $controller = new PaymentController();
        $controller->refund($payment, $request, $payumToken);
    }
}
