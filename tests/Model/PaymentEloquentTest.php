<?php

use Mockery as m;
use Recca0120\LaravelPayum\Model\Payment;

class PaymentEloquentTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_set_attributes()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payment = m::mock(new Payment());
        $creditcard = m::mock('Payum\Core\Model\CreditCardInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedNumber = 'fooNumber';
        $exceptedDetails = [
            'foo',
            'bar',
        ];
        $exceptedDescription = 'fooDescription';
        $exceptedClientEmail = 'fooClientEmail';
        $exceptedClientId = 'fooClientId';
        $exceptedTotalAmount = 'fooTotalAmount';
        $exceptedCurrencyCode = 'fooCurrencyCode';
        $exceptedCreditCard = $creditcard;

        $payment->setNumber($exceptedNumber);
        $payment->setDetails($exceptedDetails);
        $payment->setDescription($exceptedDescription);
        $payment->setClientEmail($exceptedClientEmail);
        $payment->setClientId($exceptedClientId);
        $payment->setTotalAmount($exceptedTotalAmount);
        $payment->setCurrencyCode($exceptedCurrencyCode);
        $payment->setCreditCard($exceptedCreditCard);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedNumber, $payment->getNumber());
        $this->assertSame($exceptedDetails, $payment->getDetails());
        $this->assertSame($exceptedDescription, $payment->getDescription());
        $this->assertSame($exceptedClientEmail, $payment->getClientEmail());
        $this->assertSame($exceptedClientId, $payment->getClientId());
        $this->assertSame($exceptedTotalAmount, $payment->getTotalAmount());
        $this->assertSame($exceptedCurrencyCode, $payment->getCurrencyCode());
        $this->assertSame($exceptedCreditCard, $payment->getCreditCard());
    }
}
