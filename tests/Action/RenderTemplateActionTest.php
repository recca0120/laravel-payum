<?php

use Mockery as m;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;

class RenderTemplateActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_render_template()
    {
        $renderTemplateAction = new RenderTemplateAction(
            $viewFactory = m::mock('Illuminate\Contracts\View\Factory')
        );
        $request = m::mock('Payum\Core\Request\RenderTemplate');
        $request->shouldReceive('getTemplateName')->andReturn($templateName = 'template')->once();
        $request->shouldReceive('getParameters')->andReturn($parameters = [])->once();
        $viewFactory->shouldReceive('make')->with($templateName, $parameters)->andReturnSelf()->once();
        $viewFactory->shouldReceive('render')->andReturn($html = 'html')->once();
        $request->shouldReceive('setResult')->with($html);
        $renderTemplateAction->execute($request);
    }
}
