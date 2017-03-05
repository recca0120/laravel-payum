<?php

namespace Recca0120\LaravelPayum\Model;

use Payum\Core\Model\PaymentInterface;
use Illuminate\Database\Eloquent\Model;
use Payum\Core\Model\CreditCardInterface;
use Recca0120\LaravelPayum\Contracts\PaymentStatus;

class Payment extends Model implements PaymentInterface, PaymentStatus
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
        return $this->getAttribute('clientEmail');
    }

    /**
     * setClientEmail.
     *
     * @param string $clientEmail
     */
    public function setClientEmail($clientEmail)
    {
        $this->setAttribute('clientEmail', $clientEmail);
    }

    /**
     * getClientId.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->getAttribute('clientId');
    }

    /**
     * setClientId.
     *
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->setAttribute('clientId', $clientId);
    }

    /**
     * getTotalAmount.
     *
     * @param  float
     */
    public function getTotalAmount()
    {
        return $this->getAttribute('totalAmount');
    }

    /**
     * setTotalAmount.
     *
     * @param float $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->setAttribute('totalAmount', $totalAmount);
    }

    /**
     * getCurrencyCode.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->getAttribute('currencyCode');
    }

    /**
     * setCurrencyCode.
     *
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setAttribute('currencyCode', $currencyCode);
    }

    /**
     * getCreditCard.
     *
     * @return mixed
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * setCreditCard.
     *
     * @param CreditCardInterface $creditCard
     */
    public function setCreditCard(CreditCardInterface $creditCard = null)
    {
        $this->creditCard = $creditCard;
    }

    /**
     * setStatus.
     *
     * @param CreditCardInterface $creditCard
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * getStatus.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
