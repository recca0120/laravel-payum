<?php

use Mockery as m;
use Payum\Core\Payum;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Payment;
use Recca0120\LaravelPayum\Traits\PaymentPrepare;

class PrepareControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_prepare()
    {
        $payment = m::mock(Payment::class)
            ->shouldReceive('prepare')->andReturnUsing(function ($gatewanName, $closure) {
                $storage = m::mock(StorageInterface::class);
                $payum = m::mock(Payum::class);

                return $closure(m::self(), $storage, $payum);
            })
            ->mock();
        $controller = new PrepareController();
        $controller->prepare($payment);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_throw_invalid_argumentException()
    {
        $payment = m::mock(Payment::class);
        $controller = new PrepareController2();
        $controller->prepare($payment);
    }
}

class PrepareController
{
    use PaymentPrepare;

    protected $gatewayName = 'testing';

    protected function preparePayment()
    {
    }
}

class PrepareController2
{
    use PaymentPrepare;

    protected function preparePayment()
    {
    }
}
