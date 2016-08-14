<?php

use Illuminate\Http\Request;
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
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $request = m::mock(Request::class);
        $getHttpRequestAction = new GetHttpRequestAction($request);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertAttributeSame($request, 'httpRequest', $getHttpRequestAction);
    }
}
