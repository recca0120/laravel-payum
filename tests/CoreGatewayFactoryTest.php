<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Mockery as m;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;
use Recca0120\LaravelPayum\CoreGatewayFactory;

class CoreGatewayFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_create()
    {
        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class)
            ->shouldReceive('offsetGet')->with(ReplyToSymfonyResponseConverter::class)->andReturn(m::mock(ReplyToSymfonyResponseConverter::class))
            ->shouldReceive('offsetGet')->with(GetHttpRequestAction::class)->andReturn(m::mock(GetHttpRequestAction::class))
            ->shouldReceive('offsetGet')->with(ObtainCreditCardAction::class)->andReturn(m::mock(ObtainCreditCardAction::class))
            ->shouldReceive('offsetGet')->with(RenderTemplateAction::class)->andReturn(m::mock(RenderTemplateAction::class))
            ->mock();
        $defaultConfig = [];
        $coreGateway = new CoreGatewayFactory($app, $defaultConfig);
        $coreGateway->create([
            'payum.converter.reply_to_http_response' => ReplyToSymfonyResponseConverter::class,
            'payum.action.get_http_request'          => GetHttpRequestAction::class,
            'payum.action.obtain_credit_card'        => ObtainCreditCardAction::class,
            'payum.action.render_template'           => RenderTemplateAction::class,
        ]);
    }
}
