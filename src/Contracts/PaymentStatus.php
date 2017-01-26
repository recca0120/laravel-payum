<?php

namespace Recca0120\LaravelPayum\Contracts;

interface PaymentStatus {
    /**
     * setStatus.
     *
     * @method setStatus
     *
     * @param CreditCardInterface $creditCard
     */
    public function setStatus($status);
    /**
     * getStatus.
     *
     * @method getStatus
     */
    public function getStatus();
}
