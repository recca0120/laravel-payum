<?php

namespace Recca0120\LaravelPayum\Tests\Actin;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;

class GetHttpRequestActionTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstruct()
    {
        $getHttpRequestAction = new GetHttpRequestAction($httpRequest = m::mock('Illuminate\Http\Request'));
        $this->assertAttributeSame($httpRequest, 'httpRequest', $getHttpRequestAction);
    }
}
