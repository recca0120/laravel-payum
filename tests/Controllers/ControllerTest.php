<?php

use Illuminate\Http\Request;
use Mockery as m;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;
use Recca0120\LaravelPayum\Payment;

class ControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException \Payum\Core\Reply\HttpResponse
     */
    public function test_throw_reply_interface()
    {
        $payumToken = uniqid();
        $request = m::mock(Request::class);
        $payment = m::mock(Payment::class)
            ->shouldReceive('send')->with($request, $payumToken, m::type(Closure::class))->once()->andReturnUsing(function ($request, $payumToken, $closure) {
                $token = m::mock(TokenInterface::class);

                $httpRequestVerifier = m::mock(HttpRequestVerifier::class);

                $gateway = m::mock(GatewayInterface::class)
                    ->shouldReceive('execute')->with(m::type(Capture::class))->once()->andReturnUsing(function ($capture) {
                        throw new HttpResponse('test');
                    })
                    ->mock();

                return $closure($gateway, $token, $httpRequestVerifier);
            })
            ->mock();
        $controller = new PaymentController();
        $controller->capture($payment, $request, $payumToken);
    }
}
