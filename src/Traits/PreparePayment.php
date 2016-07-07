<?php

namespace Recca0120\LaravelPayum\Traits;

use InvalidArgumentException;
use Recca0120\LaravelPayum\Payment;

trait PreparePayment
{
    /**
     * Set payment gateway name.
     *
     * @var string
     */
    // protected $gatewayName = null;

    /**
     * prepare.
     *
     * @method prepare
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param string                          $gatewayName
     *
     * @return mixed
     */
    public function prepare(Payment $payment, $gatewayName = null)
    {
        $gatewayName = $this->getGatewayName($gatewayName);
        if (empty($gatewayName) === true) {
            throw new InvalidArgumentException('Undefined property: '.static::class.'::$gatewayName');
        }

        return $payment->prepare($this->gatewayName, function ($payment, $storage, $payum) {
            return $this->onPrepare($payment);
        });
    }

    /**
     * setGatewayName.
     *
     * @method setGatewayName
     *
     * @param $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = $gatewayName;

        return $this;
    }

    /***
     * getGatewayName.
     *
     * @method getGatewayName
     *
     * @return string
     */
    protected function getGatewayName($gatewayName)
    {
        return (empty($gatewayName) === true && empty($this->gatewayName) === false) ?
            $this->gatewayName : $gatewayName;
    }

    /**
     * Prepare you payment.
     *
     * @method onPrepare
     *
     * @param \Payum\Core\Model\PaymentInterface   $payment
     * @param \Payum\Core\Storage\StorageInterface $storage
     * @param \Payum\Core\Payum
     *
     * @return \Payum\Core\Model\PaymentInterface
     */
    abstract protected function onPrepare($payment, $storage, $payum);
}
