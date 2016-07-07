<?php

use Mockery as m;
use Payum\Core\Payum;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Payment;
use Recca0120\LaravelPayum\Traits\PreparePayment;

class PrepareControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_prepare()
    {
        $payment = m::mock(Payment::class)
            ->shouldReceive('prepare')->with('testing', m::type(Closure::class))->andReturnUsing(function ($gatewanName, $closure) {
                $storage = m::mock(StorageInterface::class);
                $payum = m::mock(Payum::class);

                return $closure(m::self(), $storage, $payum);
            })
            ->mock();
        $controller = new PrepareController();
        $controller->prepare($payment);
    }

    public function test_prepare_change_gatewayname_using_method()
    {
        $payment = m::mock(Payment::class)
            ->shouldReceive('prepare')->with('testing', m::type(Closure::class))->andReturnUsing(function ($gatewanName, $closure) {
                $storage = m::mock(StorageInterface::class);
                $payum = m::mock(Payum::class);

                return $closure(m::self(), $storage, $payum);
            })
            ->mock();
        $controller = new PrepareController();
        $controller->prepare($payment, 'testing');
    }

    public function test_prepare_set_gatewayname_using_construct()
    {
        $payment = m::mock(Payment::class)
            ->shouldReceive('prepare')->with('testing', m::type(Closure::class))->andReturnUsing(function ($gatewanName, $closure) {
                $storage = m::mock(StorageInterface::class);
                $payum = m::mock(Payum::class);

                return $closure(m::self(), $storage, $payum);
            })
            ->mock();
        $controller = new PrepareSetGatewayNameController('testing');
        $controller->prepare($payment);
    }

    public function test_prepare_set_gatewayname_using_property()
    {
        $payment = m::mock(Payment::class)
            ->shouldReceive('prepare')->with('testing', m::type(Closure::class))->andReturnUsing(function ($gatewanName, $closure) {
                $storage = m::mock(StorageInterface::class);
                $payum = m::mock(Payum::class);

                return $closure(m::self(), $storage, $payum);
            })
            ->mock();
        $controller = new PrepareSetGatewayNameUsingPropertyController('testing');
        $controller->prepare($payment);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_throw_invalid_argumentException()
    {
        $payment = m::mock(Payment::class);
        $controller = new InvalidArgumentPrepareController();
        $controller->prepare($payment);
    }
}

class PrepareController
{
    use PreparePayment;

    protected $gatewayName = 'testing';

    protected function onPrepare()
    {
    }
}

class PrepareSetGatewayNameController
{
    use PreparePayment;

    public function __construct($gatewayName)
    {
        $this->setGatewayName($gatewayName);
    }

    protected function onPrepare()
    {
    }
}

class PrepareSetGatewayNameUsingPropertyController
{
    use PreparePayment;

    protected $gatewayName;

    public function __construct($gatewayName)
    {
        $this->gatewayName = $gatewayName;
    }

    protected function onPrepare()
    {
    }
}

class InvalidArgumentPrepareController
{
    use PreparePayment;

    protected function onPrepare()
    {
    }
}
