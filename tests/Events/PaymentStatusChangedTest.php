<?php

namespace Recca0120\LaravelPayum\Tests\Events;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Events\PaymentStatusChanged;

class PaymentStatusChangedTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testCreateStatusChangedEvent()
    {
        $event = new PaymentStatusChanged(
            $status = m::mock('Payum\Core\Request\GetStatusInterface'),
            $payment = m::mock('Payum\Core\Model\PaymentInterface')
        );
        $this->assertSame($status, $event->status);
        $this->assertSame($payment, $event->payment);
    }
}
