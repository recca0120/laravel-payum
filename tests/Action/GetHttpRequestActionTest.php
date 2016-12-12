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
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Illuminate\Http\Request');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $getHttpRequestAction = new GetHttpRequestAction($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertAttributeSame($request, 'httpRequest', $getHttpRequestAction);
    }
}
