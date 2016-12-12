<?php

use Mockery as m;
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
        | Arrange
        |------------------------------------------------------------
        */

        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $request = m::spy('Payum\Core\Request\RenderTemplate');
        $templateName = 'foo.template';
        $parameters = [];
        $result = 'foo';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getTemplateName')->andReturn($templateName)
            ->shouldReceive('getParameters')->andReturn($parameters);

        $viewFactory
            ->shouldReceive('make')->with($templateName, $parameters)->andReturnSelf()
            ->shouldReceive('render')->andReturn($result);

        $renderTemplateAction = new RenderTemplateAction($viewFactory);
        $renderTemplateAction->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getTemplateName')->once();
        $request->shouldHaveReceived('getParameters')->once();
        $request->shouldHaveReceived('setResult')->with($result)->once();
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function test_throw_not_support()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $viewFactory = m::spy('Illuminate\Contracts\View\Factory');
        $request = m::spy('stdClass');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $renderTemplateAction = new RenderTemplateAction($viewFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $renderTemplateAction->execute($request);
    }
}
