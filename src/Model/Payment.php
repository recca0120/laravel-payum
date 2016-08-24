<?php

namespace Recca0120\LaravelPayum\Model;

use Illuminate\Database\Eloquent\Model;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\PaymentInterface;

class Payment extends Model implements PaymentInterface
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
     * @method getNumber
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
     * @method setNumber
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
     * @method getDetails
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
     * @method setDetails
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
     * @method getDescription
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
     * @method setDescription
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
     * @method getClientEmail
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
     * @method setClientEmail
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
     * @method getClientId
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
     * @method setClientId
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
     * @method getTotalAmount
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
     * @method setTotalAmount
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
     * @method getCurrencyCode
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
     * @method setCurrencyCode
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
     * @method getCreditCard
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
     * @method setCreditCard
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
     * @method setStatus
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
     * @method getStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
