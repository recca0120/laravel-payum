<?php

namespace Recca0120\LaravelPayum\Tests\Extension;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Request\GetHumanStatus;
use Recca0120\LaravelPayum\Extension\PaymentStatusExtension;

class PaymentStatusExtensionTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testContextHasPrevious()
    {
        $paymentStatusExtension = new PaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $paymentStatusExtension->onPreExecute($context);
        $paymentStatusExtension->onExecute($context);
        $context->shouldReceive('getPrevious')->once()->andReturn(m::mock('stdClass'));
        $this->assertNull($paymentStatusExtension->onPostExecute($context));
    }

    public function testRequestIsntGeneric()
    {
        $paymentStatusExtension = new PaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->once()->andReturn($request = m::mock('stdClass'));
        $this->assertNull($paymentStatusExtension->onPostExecute($context));
    }

    public function testRequestIsntGetStatusInterface()
    {
        $paymentStatusExtension = new PaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->once()->andReturn($request = m::mock('Payum\Core\Request\GetStatusInterface'));
        $this->assertNull($paymentStatusExtension->onPostExecute($context));
    }

    public function testStatusChanged()
    {
        $paymentStatusExtension = new PaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->once()->andReturn($request = m::mock('Payum\Core\Request\Generic'));
        $request->shouldReceive('getModel')->once()->andReturn(
            $payment = m::mock('Payum\Core\Model\PaymentInterface')
        );
        $context->shouldReceive('getGateway->execute')->once()->with(m::on(function ($status) {
            $status->markCaptured();

            return $status instanceof GetHumanStatus;
        }));
        $events->shouldReceive('fire')->once()->with(m::type('Recca0120\LaravelPayum\Events\PaymentStatusChanged'));
        $this->assertNull($paymentStatusExtension->onPostExecute($context));
    }
}
