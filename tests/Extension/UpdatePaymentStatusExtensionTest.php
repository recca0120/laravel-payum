<?php

namespace Recca0120\LaravelPayum\Tests\Extension;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Request\GetHumanStatus;
use Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension;

class UpdatePaymentStatusExtensionTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testContextHasPrevious()
    {
        $updatePaymentStatusExtension = new UpdatePaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $updatePaymentStatusExtension->onPreExecute($context);
        $updatePaymentStatusExtension->onExecute($context);
        $context->shouldReceive('getPrevious')->once()->andReturn(m::mock('stdClass'));
        $this->assertNull($updatePaymentStatusExtension->onPostExecute($context));
    }

    public function testRequestIsntGeneric()
    {
        $updatePaymentStatusExtension = new UpdatePaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->once()->andReturn($request = m::mock('stdClass'));
        $this->assertNull($updatePaymentStatusExtension->onPostExecute($context));
    }

    public function testRequestIsntGetStatusInterface()
    {
        $updatePaymentStatusExtension = new UpdatePaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->once()->andReturn($request = m::mock('Payum\Core\Request\GetStatusInterface'));
        $this->assertNull($updatePaymentStatusExtension->onPostExecute($context));
    }

    public function testStatusChanged()
    {
        $updatePaymentStatusExtension = new UpdatePaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->once()->andReturn($request = m::mock('Payum\Core\Request\Generic'));
        $request->shouldReceive('getFirstModel')->once()->andReturn(
            $payment = m::mock('Payum\Core\Model\PaymentInterface, Recca0120\LaravelPayum\Contracts\PaymentStatus')
        );
        $context->shouldReceive('getGateway->execute')->once()->with(m::on(function ($status) {
            $status->markCaptured();

            return $status instanceof GetHumanStatus;
        }));
        $payment->shouldReceive('setStatus')->once()->with('captured');
        $events->shouldReceive('fire')->once()->with(m::type('Recca0120\LaravelPayum\Events\PaymentStatusChanged'));
        $this->assertNull($updatePaymentStatusExtension->onPostExecute($context));
    }
}
