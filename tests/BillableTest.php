<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Recca0120\LaravelPayum\Billable;
use Recca0120\LaravelPayum\PayumManager;

class BillableTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testCapture()
    {
        $options = ['foo' => 'bar'];
        $driver = 'foo_bar';

        $billable = new BillableStub;
        $payumManager = $this->mockPayumManager();
        $payumManager->shouldReceive('driver')->once()->andReturn(
            $payumDecorator = m::mock('Recca0120\LaravelPayum\PayumDecorator')
        );
        $payumDecorator->shouldReceive('driver')->once()->andReturn($driver);
        $payumDecorator->shouldReceive('capture')->once()->andReturnUsing(function ($closure) use ($options) {
            $paymentInterface = m::mock('Payum\Core\Model\PaymentInterface');
            list($payment, $opts) = $closure($paymentInterface, $options);

            $this->assertSame($paymentInterface, $payment);
            $this->assertSame($options, $opts);

            return 'http://localhost';
        });

        $billable->capture($options, $driver);
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

    public function captureFooBar($payment, $options)
    {
        return [$payment, $options];
    }
}
