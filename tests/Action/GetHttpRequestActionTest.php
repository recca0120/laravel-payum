<?php

use Illuminate\Http\Request;
use Mockery as m;
use Payum\Core\Request\GetHttpRequest;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;

class GetHttpRequestActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_execute()
    {
        $object = m::mock(stdClass::class)
            ->shouldReceive('all')->times(3)
            ->shouldReceive('get')->with('User-Agent')->once()
            ->mock();

        $httpRequest = m::mock(GetHttpRequest::class);
        $request = m::mock(Request::class)
            ->shouldReceive('getMethod')->once()
            ->shouldReceive('getUri')->once()
            ->shouldReceive('getClientIp')->once()
            ->shouldReceive('getContent')->once()
            ->mock();

        $request->query = $object;
        $request->request = $object;
        $request->headers = $object;

        $httpRequestAction = new GetHttpRequestAction($request);
        $httpRequestAction->execute($httpRequest);
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function test_execute_except_not_support()
    {
        $request = m::mock(Request::class);
        $httpRequestAction = new GetHttpRequestAction($request);
        $httpRequestAction->execute([]);
    }
}
