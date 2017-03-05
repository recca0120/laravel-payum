<?php

namespace Recca0120\LaravelPayum\Contracts;

interface PaymentStatus
{
    /**
     * setStatus.
     *
     * @param string $status
     */
    public function setStatus($status);

    /**
     * getStatus.
     *
     * @return string
     */
    public function getStatus();
}
