<?php
namespace Recca0120\LaravelPayum\Tests\Events;
use Mockery as m;
use Recca0120\LaravelPayum\Events\StatusChanged;

class StatusChangedTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_create_status_changed_event()
    {
        $event = new StatusChanged(
            $status = m::mock('Payum\Core\Request\GetStatusInterface'),
            $payment = m::mock('Payum\Core\Model\PaymentInterface')
        );
        $this->assertSame($status, $event->status);
        $this->assertSame($payment, $event->payment);
    }
}
