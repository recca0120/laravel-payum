<?php

namespace Recca0120\LaravelPayum\Tests\Model;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Model\Payment;

class PaymentEloquentTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testSetNumber()
    {
        $payment = new Payment();
        $payment->setNumber($number = uniqid());
        $this->assertSame($number, $payment->getNumber());
    }

    public function testSetDetails()
    {
        $payment = new Payment();
        $payment->setDetails($details = ['foo' => 'bar']);
        $this->assertSame($details, $payment->getDetails());
    }

    public function testSetDescription()
    {
        $payment = new Payment();
        $payment->setDescription($description = 'foo');
        $this->assertSame($description, $payment->getDescription());
    }

    public function testSetClientEmail()
    {
        $payment = new Payment();
        $payment->setClientEmail($clientEmail = 'recca0120@gmail.com');
        $this->assertSame($clientEmail, $payment->getClientEmail());
    }

    public function testSetClientId()
    {
        $payment = new Payment();
        $payment->setClientId($clientId = uniqid());
        $this->assertSame($clientId, $payment->getClientId());
    }

    public function testSetTotalAmount()
    {
        $payment = new Payment();
        $payment->setTotalAmount($totalAmount = 100);
        $this->assertSame($totalAmount, $payment->getTotalAmount());
    }

    public function testSetCurrencyCode()
    {
        $payment = new Payment();
        $payment->setCurrencyCode($currencyCode = 'NTD');
        $this->assertSame($currencyCode, $payment->getCurrencyCode());
    }

    public function testSetCreditcard()
    {
        $payment = new Payment();
        $payment->setCreditCard($creditCard = m::mock('Payum\Core\Model\CreditCardInterface'));
        $this->assertSame($creditCard, $payment->getCreditCard());
    }

    public function testSetStatus()
    {
        $payment = new Payment();
        $payment->setStatus($status = 'captured');
        $this->assertSame($status, $payment->getStatus());
    }
}
