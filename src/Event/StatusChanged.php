<?php

namespace Recca0120\LaravelPayum\Event;

use Payum\Core\Request\GetStatusInterface;

class StatusChanged
{
    public $status;

    public function __construct(GetStatusInterface $status)
    {
        $this->status = $status;
    }
}
