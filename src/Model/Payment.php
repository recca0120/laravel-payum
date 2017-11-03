<?php

namespace Recca0120\LaravelPayum\Model;

use Payum\Core\Model\PaymentInterface;
use Illuminate\Database\Eloquent\Model;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\BankAccountInterface;
use Payum\Core\Model\DirectDebitPaymentInterface;

class Payment extends Model implements PaymentInterface, DirectDebitPaymentInterface
{
    /**
     * $table.
     *
     * @var string
     */
    protected $table = 'payum_payments';

    /**
     * $creditCard.
     *
     * @var \Payum\Core\Model\CreditCardInterface
     */
    protected $creditCard;

    /**
     * getNumber.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->getAttribute('number');
    }

    /**
     * setNumber.
     *
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->setAttribute('number', $number);
    }

    /**
     * getDetails.
     *
     * @return mixed
     */
    public function getDetails()
    {
        return json_decode($this->getAttribute('details') ?: '{}', true);
    }

    /**
     * setDetails.
     *
     * @param mixed $details
     */
    public function setDetails($details)
    {
        $this->setAttribute('details', json_encode($details ?: []));
    }

    /**
     * getDescription.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getAttribute('description');
    }

    /**
     * setDescription.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->setAttribute('description', $description);
    }

    /**
     * getClientEmail.
     *
     * @return string
     */
    public function getClientEmail()
    {
        return $this->getAttribute('client_email');
    }

    /**
     * setClientEmail.
     *
     * @param string $clientEmail
     */
    public function setClientEmail($clientEmail)
    {
        $this->setAttribute('client_email', $clientEmail);
    }

    /**
     * getClientId.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->getAttribute('client_id');
    }

    /**
     * setClientId.
     *
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->setAttribute('client_id', $clientId);
    }

    /**
     * getTotalAmount.
     *
     * @param  float
     */
    public function getTotalAmount()
    {
        return $this->getAttribute('total_amount');
    }

    /**
     * setTotalAmount.
     *
     * @param float $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->setAttribute('total_amount', $totalAmount);
    }

    /**
     * getCurrencyCode.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->getAttribute('currency_code');
    }

    /**
     * setCurrencyCode.
     *
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setAttribute('currency_code', $currencyCode);
    }

    /**
     * getCreditCard.
     *
     * @return \Payum\Core\Model\CreditCardInterface
     */
    public function getCreditCard()
    {
        return unserialize($this->getAttribute('credit_card'));
    }

    /**
     * setCreditCard.
     *
     * @param \Payum\Core\Model\CreditCardInterface $creditCard
     */
    public function setCreditCard(CreditCardInterface $creditCard = null)
    {
        $this->setAttribute('credit_card', serialize($creditCard));
    }

    /**
     * @return BankAccountInterface|null
     */
    public function getBankAccount()
    {
        return unserialize($this->getAttribute('bank_account'));
    }

    /**
     * @param BankAccountInterface|null $bankAccount
     */
    public function setBankAccount(BankAccountInterface $bankAccount = null)
    {
        return $this->setAttribute('bank_account', serialize($bankAccount));
    }
}
