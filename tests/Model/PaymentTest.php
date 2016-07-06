<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery as m;
use Payum\Core\Model\CreditCardInterface;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class PaymentTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_update()
    {
        $number = uniqid();
        $details = [uniqid()];
        $description = uniqid();
        $clientEmail = uniqid();
        $clientId = uniqid();
        $totalAmount = 100;
        $currencyCode = 'TW';
        $creditcard = m::mock(CreditCardInterface::class);

        $paymentClass = EloquentPayment::class;

        $payment = m::mock(new $paymentClass())
            ->shouldReceive('save')->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($paymentClass)->once()->andReturn($payment)
            ->mock();

        $storage = new EloquentStorage($app, $paymentClass);
        $payment = $storage->create();

        $payment->setNumber($number);
        $payment->setDetails($details);
        $payment->setDescription($description);
        $payment->setClientEmail($clientEmail);
        $payment->setClientId($clientId);
        $payment->setTotalAmount($totalAmount);
        $payment->setCurrencyCode($currencyCode);
        $payment->setCreditcard($creditcard);

        $this->assertSame($payment->getNumber(), $number);
        $this->assertSame($payment->getDetails(), $details);
        $this->assertSame($payment->getDescription(), $description);
        $this->assertSame($payment->getClientEmail(), $clientEmail);
        $this->assertSame($payment->getClientId(), $clientId);
        $this->assertSame($payment->getTotalAmount(), $totalAmount);
        $this->assertSame($payment->getCurrencyCode(), $currencyCode);
        $this->assertSame($payment->getCreditcard(), $creditcard);

        $storage->update($payment);
    }

    public function test_delete()
    {
        $paymentClass = EloquentPayment::class;

        $payment = m::mock(new $paymentClass())
            ->shouldReceive('delete')->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($paymentClass)->once()->andReturn($payment)
            ->mock();

        $storage = new EloquentStorage($app, $paymentClass);
        $payment = $storage->create();

        $storage->delete($payment);
    }

    public function test_find()
    {
        $paymentClass = EloquentPayment::class;

        $payment = m::mock(new $paymentClass())
            ->shouldReceive('find')->with(1)->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($paymentClass)->once()->andReturn($payment)
            ->mock();

        $storage = new EloquentStorage($app, $paymentClass);
        $payment = $storage->find(1);
    }

    public function test_identify()
    {
        $hash = uniqid();

        $paymentClass = EloquentPayment::class;
        $payment = m::mock(new $paymentClass())
            ->shouldReceive('getKey')->andReturn($hash)
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($paymentClass)->once()->andReturn($payment)
            ->mock();

        $storage = new EloquentStorage($app, $paymentClass);
        $payment = $storage->create();

        $this->assertSame($storage->identify($payment)->getId(), $hash);
    }

    public function test_find_by()
    {
        $clientId = uniqid();
        $clientEmail = uniqid();

        $builder = m::mock(Builder::class)
            ->shouldReceive('where')->with('clientId', '=', $clientId)->once()->andReturnSelf()
            ->shouldReceive('where')->with('clientEmail', '=', $clientEmail)->once()->andReturnSelf()
            ->shouldReceive('get')->andReturn(new Collection())
            ->mock();

        $tokenClass = EloquentPayment::class;
        $token = m::mock(new $tokenClass())
            ->shouldReceive('newQuery')->once()->andReturn($builder)
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($tokenClass)->once()->andReturn($token)
            ->mock();

        $storage = new EloquentStorage($app, $tokenClass);
        $storage->findBy([
            'clientId'      => $clientId,
            'clientEmail'   => $clientEmail,
        ]);
    }
}
