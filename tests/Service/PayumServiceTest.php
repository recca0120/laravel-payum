<?php

namespace Recca0120\LaravelPayum\Tests\Service;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Reply\HttpResponse;
use Recca0120\LaravelPayum\Service\PayumService;

class PayumServiceTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_get_payum()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payum = m::spy('\Payum\Core\Payum');
        $request = m::spy('\Illuminate\Http\Request');
        $responseFactory = m::spy('\Illuminate\Contracts\Routing\ResponseFactory');
        $converter = m::spy('\Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumService = new PayumService($payum, $request, $responseFactory, $converter);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($payum, $payumService->getPayum());
    }

    public function test_get_gateway()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payum = m::spy('Payum\Core\Payum');
        $request = m::spy('Illuminate\Http\Request');
        $responseFactory = m::spy('Illuminate\Contracts\Routing\ResponseFactory');
        $converter = m::spy('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');

        $gateway = m::spy('Payum\Core\GatewayInterface');
        $gatewayName = 'foo.gateway';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payum
            ->shouldReceive('getGateway')->andReturn($gateway);

        $payumService = new PayumService($payum, $request, $responseFactory, $converter);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($gateway, $payumService->getGateway($gatewayName));
        $payum->shouldHaveReceived('getGateway')->with($gatewayName)->once();
    }

    public function test_prepare()
    {
        $this->validateRequest('prepare', 'Capture');
    }

    public function test_request()
    {
        $this->validateRequest('request', 'Capture');
    }

    public function test_capture()
    {
        $this->validateRequest('capture', 'Capture');
    }

    public function test_authorize()
    {
        $this->validateRequest('authorize', 'Authorize');
    }

    public function test_refund()
    {
        $this->validateRequest('refund', 'Refund');
    }

    public function test_cancel()
    {
        $this->validateRequest('cancel', 'Cancel');
    }

    public function test_payout()
    {
        $this->validateRequest('payout', 'Payout');
    }

    public function test_receive_authorize()
    {
        $this->validateReceive('Authorize');
    }

    public function test_receive_authorize_throw_reply()
    {
        $this->validateReceive('Authorize', true);
    }

    public function test_receive_capture()
    {
        $this->validateReceive('Capture');
    }

    public function test_receive_capture_throw_reply()
    {
        $this->validateReceive('Capture', true);
    }

    public function test_receive_notify()
    {
        $this->validateReceive('Notify');
    }

    public function test_receive_notify_throw_reply()
    {
        $this->validateReceive('Notify', true);
    }

    public function test_receive_notify_unsafe()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payum = m::spy('Payum\Core\Payum');
        $request = m::spy('Illuminate\Http\Request');
        $responseFactory = m::spy('Illuminate\Contracts\Routing\ResponseFactory');
        $converter = m::spy('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');

        $gatewayName = 'foo.gateway';
        $gateway = m::spy('Payum\Core\GatewayInterface');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payum
            ->shouldReceive('getGateway')->with($gatewayName)->andReturn($gateway);

        $payumService = new PayumService($payum, $request, $responseFactory, $converter);
        $payumService->receiveNotifyUnsafe($gatewayName);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $payum->shouldHaveReceived('getGateway')->with($gatewayName)->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\Notify'))->once();
        $responseFactory->shouldHaveReceived('make')->with(null, 204);
    }

    public function test_receive_notify_unsafe_throw_reply()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payum = m::spy('Payum\Core\Payum');
        $request = m::spy('Illuminate\Http\Request');
        $responseFactory = m::spy('Illuminate\Contracts\Routing\ResponseFactory');
        $converter = m::spy('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');

        $gatewayName = 'foo.gateway';
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $throwResponse = new HttpResponse('testing');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payum
            ->shouldReceive('getGateway')->with($gatewayName)->andReturn($gateway);

        $responseFactory
            ->shouldReceive('make')->with(null, 204)->andReturnUsing(function () use ($throwResponse) {
                throw $throwResponse;
            });

        $payumService = new PayumService($payum, $request, $responseFactory, $converter);
        $payumService->receiveNotifyUnsafe($gatewayName);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $payum->shouldHaveReceived('getGateway')->with($gatewayName)->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\Notify'))->once();
        $responseFactory->shouldHaveReceived('make')->with(null, 204);
        $converter->shouldHaveReceived('convert')->with($throwResponse)->once();
    }

    public function test_receive_payout()
    {
        $this->validateReceive('Payout');
    }

    public function test_receive_payout_throw_reply()
    {
        $this->validateReceive('Payout', true);
    }

    public function test_receive_cancel()
    {
        $this->validateReceive('Cancel');
    }

    public function test_receive_cancel_throw_reply()
    {
        $this->validateReceive('Cancel', true);
    }

    public function test_receive_cancel_without_after_url()
    {
        $this->validateReceive('Cancel', false, true);
    }

    public function test_receive_refund()
    {
        $this->validateReceive('Refund');
    }

    public function test_receive_refund_throw_reply()
    {
        $this->validateReceive('Refund', true);
    }

    public function test_receive_refund_without_after_url()
    {
        $this->validateReceive('Refund', false, true);
    }

    public function test_receive_sync()
    {
        $this->validateReceive('Sync');
    }

    public function test_receive_sync_throw_reply()
    {
        $this->validateReceive('Sync', true);
    }

    public function test_done()
    {
        $this->validateReceive('Done');
    }

    public function test_sync()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payum = m::spy('Payum\Core\Payum');
        $request = m::spy('Illuminate\Http\Request');
        $responseFactory = m::spy('Illuminate\Contracts\Routing\ResponseFactory');
        $converter = m::spy('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');

        $gatewayName = 'foo.gateway';
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $closure = function () {
        };

        $paymentModel = 'Recca0120\LaravelPayum\Model\Payment';
        $payment = m::spy($paymentModel);

        $storages = [
            $paymentModel,
        ];

        $storage = m::spy('Payum\Core\Storage\StorageInterface');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payum
            ->shouldReceive('getGateway')->with($gatewayName)->andReturn($gateway)
            ->shouldReceive('getStorages')->andReturn($storages)
            ->shouldReceive('getStorage')->andReturn($storage);

        $storage
            ->shouldReceive('create')->andReturn($payment);

        $payumService = new PayumService($payum, $request, $responseFactory, $converter);
        $payumService->sync($gatewayName, $closure);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $payum->shouldHaveReceived('getGateway')->with($gatewayName)->once();
        $payum->shouldHaveReceived('getStorages')->once();
        $payum->shouldHaveReceived('getStorage')->once();
        $storage->shouldHaveReceived('create')->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\Convert'))->once();
        $payment->shouldHaveReceived('setDetails')->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\Sync'))->once();
    }

    public function validateReceive($type, $throwException = false, $withoutAfterUrl = false)
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payum = m::spy('Payum\Core\Payum');
        $request = m::spy('Illuminate\Http\Request');
        $responseFactory = m::spy('Illuminate\Contracts\Routing\ResponseFactory');
        $converter = m::spy('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');

        $payumToken = uniqid();

        $session = m::spy('Illuminate\Session\SessionInterface');
        $httpRequestVerifier = m::spy('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::spy('Payum\Core\Security\TokenInterface');

        $gatewayName = 'foo.gateway';

        $gateway = m::spy('Payum\Core\GatewayInterface');

        $afterUrl = ($withoutAfterUrl === true) ? '' : 'foo.after_url';

        $throwResponse = new HttpResponse('testing');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('session')->andReturn($session)
            ->shouldReceive('duplicate')->andReturnSelf();

        $session
            ->shouldReceive('isStarted')->andReturn(false)
            ->shouldReceive('get')->with('payum_token')->andReturn($payumToken);

        $payum
            ->shouldReceive('getHttpRequestVerifier')->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->andReturn($gateway);

        $httpRequestVerifier
            ->shouldReceive('verify')->with($request)->andReturn($token);

        $token
            ->shouldReceive('getGatewayName')->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->andReturn($afterUrl);

        if ($throwException === true) {
            $responseFactory
                ->shouldReceive('redirectTo')->with($afterUrl)->andReturnUsing(function () use ($throwResponse) {
                    throw $throwResponse;
                });
        }

        $payumService = new PayumService($payum, $request, $responseFactory, $converter);
        call_user_func_array([$payumService, 'receive'.$type], [null, function () {
        }]);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('session')->atLeast(1);
        $session->shouldHaveReceived('isStarted')->atLeast(1);
        $session->shouldHaveReceived('start')->atLeast(1);
        $session->shouldHaveReceived('get')->with('payum_token')->once();
        $session->shouldHaveReceived('forget')->with('payum_token')->once();
        $session->shouldHaveReceived('save')->atLeast(1);
        $request->shouldHaveReceived('duplicate')->once();
        $request->shouldHaveReceived('merge')->with(['payum_token' => $payumToken])->once();
        $payum->shouldHaveReceived('getHttpRequestVerifier')->once();
        $httpRequestVerifier->shouldHaveReceived('verify')->with($request)->once();
        $token->shouldHaveReceived('getGatewayName')->once();

        switch ($type) {
            case 'Notify':
                $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\\'.$type))->once();
                $responseFactory->shouldHaveReceived('make')->with(null, 204);
                break;
            case 'Done':
                $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHumanStatus'))->once();
                break;
            default:
                $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\\'.$type))->once();
                $httpRequestVerifier->shouldHaveReceived('invalidate')->with($token)->once();
                $token->shouldHaveReceived('getAfterUrl')->once();

                if ($withoutAfterUrl === true) {
                    $responseFactory->shouldHaveReceived('make')->with(null, 204);
                } else {
                    $responseFactory->shouldHaveReceived('redirectTo')->with($afterUrl)->once();
                }

                if ($throwException === true) {
                    $session->shouldHaveReceived('put')->with('payum_token', $payumToken)->once();
                    $converter->shouldHaveReceived('convert')->with($throwResponse)->once();
                }

                break;
        }
    }

    protected function validateRequest($method, $tokenType)
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payum = m::spy('Payum\Core\Payum');
        $request = m::spy('Illuminate\Http\Request');
        $responseFactory = m::spy('Illuminate\Contracts\Routing\ResponseFactory');
        $converter = m::spy('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');

        $gateway = m::spy('Payum\Core\GatewayInterface');
        $storage = m::spy('Payum\Core\Storage\StorageInterface');

        $tokenFactory = m::spy('Payum\Core\Security\TokenFactoryInterface');
        $token = m::spy('Payum\Core\Security\TokenInterface');

        $gatewayName = 'foo.gateway';
        $closure = function () {
        };
        $afterPath = 'foo.done';
        $afterParameters = [];

        $paymentModel = 'Recca0120\LaravelPayum\Model\Payment';
        $payment = m::spy($paymentModel);

        $storages = [
            $paymentModel,
        ];

        $createMethod = 'create'.ucfirst($tokenType).'Token';

        $targetUrl = 'foo.target_url';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payum
            ->shouldReceive('getStorages')->andReturn($storages)
            ->shouldReceive('getStorage')->andReturn($storage)
            ->shouldReceive('getTokenFactory')->andReturn($tokenFactory);

        $storage
            ->shouldReceive('create')->andReturn($payment)
            ->shouldReceive('update');

        $tokenFactory
            ->shouldReceive($createMethod)->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->andReturn($targetUrl);

        $payumService = new PayumService($payum, $request, $responseFactory, $converter);
        call_user_func_array([$payumService, $method], [$gatewayName, $closure, $afterPath, $afterParameters, $tokenType]);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $payum->shouldHaveReceived('getStorages')->once();
        $payum->shouldHaveReceived('getStorage')->with($paymentModel)->once();
        $storage->shouldHaveReceived('create')->once();
        $storage->shouldHaveReceived('update')->with($payment)->once();
        $payum->shouldHaveReceived('getTokenFactory')->once();
        $tokenFactory->shouldHaveReceived($createMethod)->with(
            $gatewayName,
            $payment,
            $afterPath,
            $afterParameters
        )->once();
        $token->shouldHaveReceived('getTargetUrl')->once();
        $responseFactory->shouldHaveReceived('redirectTo')->with($targetUrl)->once();
    }
}
