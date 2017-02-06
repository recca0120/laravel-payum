<?php

namespace Recca0120\LaravelPayum\Tests\Events;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Events\StatusChanged;

class StatusChangedTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCreateStatusChangedEvent()
    {
        $event = new StatusChanged(
            $status = m::mock('Payum\Core\Request\GetStatusInterface'),
            $payment = m::mock('Payum\Core\Model\PaymentInterface')
        );
        $this->assertSame($status, $event->status);
        $this->assertSame($payment, $event->payment);
    }
}
