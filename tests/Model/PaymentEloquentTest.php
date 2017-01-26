<?php

use Mockery as m;
use Recca0120\LaravelPayum\Model\Payment;

class PaymentEloquentTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_set_number()
    {
        $payment = new Payment();
        $payment->setNumber($number = uniqid());
        $this->assertSame($number, $payment->getNumber());
    }

    public function test_set_details()
    {
        $payment = new Payment();
        $payment->setDetails($details = ['foo' => 'bar']);
        $this->assertSame($details, $payment->getDetails());
    }

    public function test_set_description()
    {
        $payment = new Payment();
        $payment->setDescription($description = 'foo');
        $this->assertSame($description, $payment->getDescription());
    }

    public function test_set_client_email()
    {
        $payment = new Payment();
        $payment->setClientEmail($clientEmail = 'recca0120@gmail.com');
        $this->assertSame($clientEmail, $payment->getClientEmail());
    }

    public function test_set_client_id()
    {
        $payment = new Payment();
        $payment->setClientId($clientId = uniqid());
        $this->assertSame($clientId, $payment->getClientId());
    }

    public function test_set_total_amount()
    {
        $payment = new Payment();
        $payment->setTotalAmount($totalAmount = 100);
        $this->assertSame($totalAmount, $payment->getTotalAmount());
    }

    public function test_set_currency_code()
    {
        $payment = new Payment();
        $payment->setCurrencyCode($currencyCode = 'NTD');
        $this->assertSame($currencyCode, $payment->getCurrencyCode());
    }

    public function test_set_creditcard()
    {
        $payment = new Payment();
        $payment->setCreditCard($creditCard = m::mock('Payum\Core\Model\CreditCardInterface'));
        $this->assertSame($creditCard, $payment->getCreditCard());
    }

    public function test_set_status()
    {
        $payment = new Payment();
        $payment->setStatus($status = 'captured');
        $this->assertSame($status, $payment->getStatus());
    }
}
