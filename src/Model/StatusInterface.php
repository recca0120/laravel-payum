<?php

namespace Recca0120\LaravelPayum\Model;

interface StatusInterface {
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
