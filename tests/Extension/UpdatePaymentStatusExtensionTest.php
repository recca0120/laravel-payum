<?php

use Mockery as m;
use Payum\Core\Extension\Context;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension;
use Recca0120\LaravelPayum\Model\Payment;
use Illuminate\Contracts\Events\Dispatcher;
use Recca0120\LaravelPayum\Event\StatusChanged;

class UpdatePaymentStatusExtensionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_when_context_get_previous()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $event = m::mock(Dispatcher::class);
        $extension = new UpdatePaymentStatusExtension($event);
        $context = m::mock(Context::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $context->shouldReceive('getPrevious')->andReturn(true);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertNull($extension->onPostExecute($context));
    }

    public function test_when_request_is_not_instanceof_generic()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $event = m::mock(Dispatcher::class);
        $extension = new UpdatePaymentStatusExtension($event);
        $context = m::mock(Context::class);
        $request = m::mock(stdClass::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $context
            ->shouldReceive('getPrevious')->andReturn(false)
            ->shouldReceive('getRequest')->andReturn($request);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertNull($extension->onPostExecute($context));
    }

    public function test_when_request_is_instanceof_get_status_interface()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $event = m::mock(Dispatcher::class);
        $extension = new UpdatePaymentStatusExtension($event);
        $context = m::mock(Context::class);
        $request = m::mock(GetStatusInterface::class.','.Generic::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $context
            ->shouldReceive('getPrevious')->andReturn(false)
            ->shouldReceive('getRequest')->andReturn($request);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertNull($extension->onPostExecute($context));
    }

    public function test_when_request_is_instanceof_payment_interface()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $event = m::mock(Dispatcher::class);
        $extension = new UpdatePaymentStatusExtension($event);
        $context = m::mock(Context::class);
        $request = m::mock(Generic::class);
        $payment = new Payment();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $context
            ->shouldReceive('getPrevious')->andReturn(false)
            ->shouldReceive('getRequest')->andReturn($request)
            ->shouldReceive('getGateway->execute')->andReturnUsing(function ($status) {
                $status->markPending();
            });

        $request
            ->shouldReceive('getFirstModel')->andReturn($payment);

        $event->shouldReceive('fire')->with(m::type(StatusChanged::class))->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $extension->onPreExecute($context);
        $extension->onExecute($context);
        $this->assertNull($extension->onPostExecute($context));
        $this->assertSame(GetHumanStatus::STATUS_PENDING, $payment->getStatus());
    }
}
