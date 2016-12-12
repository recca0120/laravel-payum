<?php

use Mockery as m;
use Payum\Core\Model\Payment;
use Payum\Core\Request\GetHumanStatus;
use Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension;

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
        | Arrange
        |------------------------------------------------------------
        */

        $event = m::spy('Illuminate\Contracts\Events\Dispatcher');
        $context = m::spy('Payum\Core\Extension\Context');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $context
            ->shouldReceive('getPrevious')->andReturn(true);

        $extension = new UpdatePaymentStatusExtension($event);
        $extension->onPostExecute($context);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $context->shouldHaveReceived('getPrevious')->once();
    }

    public function test_when_request_is_not_instanceof_generic()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $event = m::spy('Illuminate\Contracts\Events\Dispatcher');
        $context = m::spy('Payum\Core\Extension\Context');
        $request = m::spy('stdClass');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $context
            ->shouldReceive('getPrevious')->andReturn(false)
            ->shouldReceive('getRequest')->andReturn($request);

        $extension = new UpdatePaymentStatusExtension($event);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertNull($extension->onPostExecute($context));

        $context->shouldHaveReceived('getPrevious')->once();
        $context->shouldHaveReceived('getRequest')->once();
    }

    public function test_when_request_is_instanceof_get_status_interface()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $event = m::spy('Illuminate\Contracts\Events\Dispatcher');
        $context = m::spy('Payum\Core\Extension\Context');
        $request = m::spy('Payum\Core\Request\GetStatusInterface ,Payum\Core\Request\Generic');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $context
            ->shouldReceive('getPrevious')->andReturn(false)
            ->shouldReceive('getRequest')->andReturn($request);

        $extension = new UpdatePaymentStatusExtension($event);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertNull($extension->onPostExecute($context));
        $context->shouldHaveReceived('getPrevious')->once();
        $context->shouldHaveReceived('getRequest')->once();
    }

    public function test_when_request_is_instanceof_payment_interface()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $event = m::spy('Illuminate\Contracts\Events\Dispatcher');
        $context = m::spy('Payum\Core\Extension\Context');
        $request = m::spy('Payum\Core\Request\Generic');
        $payment = new PaymentTest();

        /*
        |------------------------------------------------------------
        | Act
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

        $extension = new UpdatePaymentStatusExtension($event);
        $extension->onPreExecute($context);
        $extension->onExecute($context);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertNull($extension->onPostExecute($context));

        $context->shouldHaveReceived('getPrevious')->once();
        $context->shouldHaveReceived('getRequest')->once();
        $request->shouldHaveReceived('getFirstModel')->once();
        $context->shouldHaveReceived('getGateway')->once();
        $event->shouldHaveReceived('fire')->once();

        $this->assertSame(GetHumanStatus::STATUS_PENDING, $payment->getStatus());
    }
}

class PaymentTest extends Payment
{
    protected $status;

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
