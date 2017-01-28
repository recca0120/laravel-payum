<?php
namespace Recca0120\LaravelPayum\Tests\Extension;
use Mockery as m;
use Payum\Core\Model\Payment;
use Payum\Core\Request\GetHumanStatus;
use Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension;

class UpdatePaymentStatusExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_context_has_previous()
    {
        $updatePaymentStatusExtension = new UpdatePaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $updatePaymentStatusExtension->onPreExecute($context);
        $updatePaymentStatusExtension->onExecute($context);
        $context->shouldReceive('getPrevious')->andReturn(m::mock('stdClass'))->once();
        $updatePaymentStatusExtension->onPostExecute($context);
    }

    public function test_request_isnt_generic()
    {
        $updatePaymentStatusExtension = new UpdatePaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->andReturn($request = m::mock('stdClass'))->once();
        $updatePaymentStatusExtension->onPostExecute($context);
    }

    public function test_request_isnt_get_status_interface()
    {
        $updatePaymentStatusExtension = new UpdatePaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->andReturn($request = m::mock('Payum\Core\Request\GetStatusInterface'))->once();
        $updatePaymentStatusExtension->onPostExecute($context);
    }

    public function test_status_changed()
    {
        $updatePaymentStatusExtension = new UpdatePaymentStatusExtension(
            $events = m::mock('Illuminate\Contracts\Events\Dispatcher')
        );
        $context = m::mock('Payum\Core\Extension\Context');
        $context->shouldReceive('getPrevious')->once();
        $context->shouldReceive('getRequest')->andReturn($request = m::mock('Payum\Core\Request\Generic'))->once();
        $request->shouldReceive('getFirstModel')->andReturn($payment = m::mock('Payum\Core\Model\PaymentInterface, Recca0120\LaravelPayum\Contracts\PaymentStatus'))->once();
        $context->shouldReceive('getGateway->execute')->with(m::on(function ($status) {
            $status->markCaptured();

            return $status instanceof GetHumanStatus;
        }))->once();
        $payment->shouldReceive('setStatus')->with('captured')->once();
        $events->shouldReceive('fire')->with(m::type('Recca0120\LaravelPayum\Events\StatusChanged'))->once();
        $updatePaymentStatusExtension->onPostExecute($context);
    }
}
