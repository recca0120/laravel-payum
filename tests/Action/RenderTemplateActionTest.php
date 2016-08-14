<?php

use Illuminate\Contracts\View\Factory;
use Mockery as m;
use Payum\Core\Request\RenderTemplate;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;

class RenderTemplateActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_execute()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $viewFactory = m::mock(Factory::class);
        $renderTemplateAction = new RenderTemplateAction($viewFactory);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedTemplateName = 'foo';
        $exceptedParameters = [
            'foo',
            'bar',
        ];
        $excepted = 'foobar';
        $request = new RenderTemplate($exceptedTemplateName, $exceptedParameters);

        $viewFactory->shouldReceive('make')->with($exceptedTemplateName, $exceptedParameters)->andReturnSelf()
            ->shouldReceive('render')->andReturn($excepted);

        $renderTemplateAction->execute($request);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedTemplateName, $request->getTemplateName());
        $this->assertSame($exceptedParameters, $request->getParameters());
        $this->assertSame($excepted, $request->getResult());
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function test_throw_not_support()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $viewFactory = m::mock(Factory::class);
        $renderTemplateAction = new RenderTemplateAction($viewFactory);
        $request = m::mock(stdClass::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $renderTemplateAction->execute($request);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
    }
}
