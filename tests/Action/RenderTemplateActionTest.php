<?php

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
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
        $view = m::mock(View::class)
            ->shouldReceive('render')->andReturn('body')
            ->mock();

        $viewFactory = m::mock(ViewFactory::class)
            ->shouldReceive('make')->with('abc', ['a' => 'b'])->andReturn($view)
            ->mock();

        $renderTemplate = m::mock(RenderTemplate::class)
            ->shouldReceive('getTemplateName')->andReturn('abc')
            ->shouldReceive('getParameters')->andReturn(['a' => 'b'])
            ->shouldReceive('setResult')->with('body')
            ->mock();

        $renderTemplateAction = new RenderTemplateAction($viewFactory);
        $renderTemplateAction->execute($renderTemplate);
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function test_execute_except_not_support()
    {
        $viewFactory = m::mock(ViewFactory::class);
        $renderTemplateAction = new RenderTemplateAction($viewFactory);
        $renderTemplateAction->execute([]);
    }
}
