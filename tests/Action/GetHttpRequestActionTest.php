<?php

use Mockery as m;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;

class GetHttpRequestActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_construct()
    {
        $getHttpRequestAction = new GetHttpRequestAction($httpRequest = m::mock('Illuminate\Http\Request'));
        $this->assertAttributeSame($httpRequest, 'httpRequest', $getHttpRequestAction);
    }
}
