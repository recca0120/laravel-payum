<?php

namespace Recca0120\LaravelPayum\Tests\Actin;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;

class RenderTemplateActionTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testRenderTemplate()
    {
        $renderTemplateAction = new RenderTemplateAction(
            $viewFactory = m::mock('Illuminate\Contracts\View\Factory')
        );
        $request = m::mock('Payum\Core\Request\RenderTemplate');
        $request->shouldReceive('getTemplateName')->once()->andReturn($templateName = 'template');
        $request->shouldReceive('getParameters')->once()->andReturn($parameters = []);
        $viewFactory->shouldReceive('make')->once()->with($templateName, $parameters)->andReturnSelf();
        $viewFactory->shouldReceive('render')->once()->andReturn($html = 'html');
        $request->shouldReceive('setResult')->once()->with($html);
        $this->assertNull($renderTemplateAction->execute($request));
    }
}
