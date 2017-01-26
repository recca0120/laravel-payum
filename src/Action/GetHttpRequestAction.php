<?php

namespace Recca0120\LaravelPayum\Action;

use Illuminate\Http\Request;
use Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction as SymfonyGetHttpRequestAction;

class GetHttpRequestAction extends SymfonyGetHttpRequestAction
{
    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Http\Request $httpRequest
     */
    public function __construct(Request $httpRequest)
    {
        $this->setHttpRequest($httpRequest);
    }
}
