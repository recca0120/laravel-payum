<?php

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Mockery as m;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Payment;

class PaymentServiceTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_get_payum()
    {
        $payum = m::mock(Payum::class);
        $sessionManager = m::mock(SessionManager::class);
        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $payment = new Payment($payum, $sessionManager, $converter);
        $this->assertSame($payment->getPayum(), $payum);
    }

    public function test_prepare()
    {
        $gatewayName = 'gatewayName';

        $payment = m::mock(EloquentPayment::class)
            ->shouldReceive('setClientEmail')->once()
            ->mock();

        $storage = m::mock(stdClass::class)
            ->shouldReceive('create')->andReturn($payment)
            ->shouldReceive('update')->andReturn($payment)
            ->mock();

        $captureToken = m::mock(stdClass::class)
            ->shouldReceive('getTargetUrl')->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getStorages')->andReturn([
                EloquentPayment::class => 'storage',
            ])
            ->shouldReceive('getStorage')->andReturn($storage)
            ->shouldReceive('getTokenFactory')->andReturnSelf()
            ->shouldReceive('createCaptureToken')->andReturn($captureToken)
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $payment = new Payment($payum, $sessionManager, $converter);
        $payment->prepare($gatewayName, function ($payment) {
            $payment->setClientEmail('test@test.com');
        });
    }

    public function test_done()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('merge')
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $payment = new Payment($payum, $sessionManager, $converter);
        $payment->done($request, $token, function ($payment) {
        });
    }

    public function test_do_action()
    {
        $payumToken = uniqid();

        $gatewayName = 'test';

        $gateway = m::mock(GatewayInterface::class);

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with(['payum_token' => $payumToken])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->andReturn($gateway)
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $payment = new Payment($payum, $sessionManager, $converter);
        $payment->send($request, $payumToken, function ($p1, $p2, $p3) use ($gateway, $token, $httpRequestVerifier) {
            $this->assertSame($p1, $gateway);
            $this->assertSame($p2, $token);
            $this->assertSame($p3, $httpRequestVerifier);
        });
    }

    public function test_do_action_with_null_payum_token()
    {
        $payumToken = uniqid();

        $gatewayName = 'test';

        $gateway = m::mock(GatewayInterface::class);

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with(['payum_token' => $payumToken])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->andReturn($gateway)
            ->mock();

        $sessionManager = m::mock(SessionManager::class)
            ->shouldReceive('get')->with('payum_token')->andReturn($payumToken)
            ->shouldReceive('forget')->with('payum_token')
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $payment = new Payment($payum, $sessionManager, $converter);
        $payment->send($request, null, function ($p1, $p2, $p3) use ($gateway, $token, $httpRequestVerifier) {
            $this->assertSame($p1, $gateway);
            $this->assertSame($p2, $token);
            $this->assertSame($p3, $httpRequestVerifier);
        });
    }

    public function test_throw_reply_interface()
    {
        $payumToken = uniqid();

        $gatewayName = 'test';

        $gateway = m::mock(GatewayInterface::class);

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with(['payum_token' => $payumToken])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->andReturn($gateway)
            ->mock();

        $sessionManager = m::mock(SessionManager::class)
            ->shouldReceive('get')->with('payum_token')->andReturn($payumToken)
            ->shouldReceive('forget')->with('payum_token')
            ->shouldReceive('set')->with('payum_token', $payumToken)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class)
            ->shouldReceive('convert')->with(m::type(ReplyInterface::class))
            ->mock();

        $payment = new Payment($payum, $sessionManager, $converter);
        $payment->send($request, $payumToken, function () {
            throw new HttpResponse('testing');
        });
    }
}
