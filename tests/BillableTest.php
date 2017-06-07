<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Recca0120\LaravelPayum\Billable;
use Payum\Core\Model\PaymentInterface;
use Recca0120\LaravelPayum\PayumManager;

class BillableTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testGetPayumManager()
    {
        $this->mockPayumManager();
        $billable = new BillableStub;
        $this->assertInstanceOf(PayumManager::class, $billable->getPayumManager());
    }

    public function testCharge()
    {
        $billable = new BillableStub;
        $amount = 100;
        $options = [];
        $payumManager = $this->mockPayumManager();
        $payumManager->shouldReceive('getGatewayName')->once()->andReturn($gatewayName = 'foo_bar');
        $payumManager->shouldReceive('capture')->once()->with(m::on(function ($closure) use ($billable, $amount, $options) {
            $payment = m::mock(PaymentInterface::class);
            $this->assertSame([
                $payment, $amount, $options,
            ], $closure($payment));

            return true;
        }));

        $billable->charge($amount, $options);
    }

    protected function mockPayumManager()
    {
        $payumManager = m::mock(PayumManager::class);
        $container = new Container();
        $container->instance(PayumManager::class, $payumManager);
        $container->setInstance($container);

        return $payumManager;
    }
}

class BillableStub
{
    use Billable;

    public function chargeFooBar($payment, $amount, $options)
    {
        return [$payment, $amount, $options];
    }
}
