<?php

use Mockery as m;
use Recca0120\LaravelPayum\LaravelPayumServiceProvider;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;
use Recca0120\LaravelPayum\PayumBuilderWrapper;

class LaravelPayumServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_register_service_provider()
    {
        $serviceProvider = new LaravelPayumServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $app->shouldReceive('offsetGet')->with('config')->andReturn($config = m::mock('stdClass'))->twice();
        $config->shouldReceive('get')->with('payum', [])->andReturn([]);
        $config->shouldReceive('set')->with('payum', m::type('array'))->andReturn([]);
        $app->shouldReceive('singleton')->with('Recca0120\LaravelPayum\PayumBuilderWrapper', m::on(function ($closure) use ($app) {
            $app->shouldReceive('offsetGet')->with('config')->andReturn(['payum' => ['path' => 'foo']])->once();

            return $closure($app) instanceof PayumBuilderWrapper;
        }))->once();
        $app->shouldReceive('singleton')->with('Payum\Core\PayumBuilder', m::on(function ($closure) use ($app) {
            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Action\GetHttpRequestAction')->andReturn($getHttpRequestAction = 'GetHttpRequestAction')->once();
            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Action\ObtainCreditCardAction')->andReturn($obtainCreditCardAction = 'ObtainCreditCardAction')->once();
            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Action\RenderTemplateAction')->andReturn($renderTemplateAction = 'RenderTemplateAction')->once();
            $app->shouldReceive('make')->with('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')->andReturn($replyToSymfonyResponseConverter = 'ReplyToSymfonyResponseConverter')->once();
            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension')->andReturn($updatePaymentStatusExtension = 'UpdatePaymentStatusExtension')->once();

            $app->shouldReceive('offsetGet')->with('url')->andReturn(
                $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
            )->once();
            $app->shouldReceive('offsetGet')->with('files')->andReturn(
                $files = m::mock('Illuminate\Filesystem\Filesystem')
            );

            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\PayumBuilderWrapper')->andReturn(
                $payumBuilderWrapper = m::mock('Recca0120\LaravelPayum\PayumBuilderWrapper')
            )->once();
            $payumBuilderWrapper->shouldReceive('setTokenFactory')->with($urlGenerator)->andReturnSelf()->once();
            $payumBuilderWrapper->shouldReceive('setStorage')->with($files)->andReturnSelf()->once();
            $payumBuilderWrapper->shouldReceive('setCoreGatewayFactoryConfig')->with([
                'payum.action.get_http_request' => $getHttpRequestAction,
                'payum.action.obtain_credit_card' => $obtainCreditCardAction,
                'payum.action.render_template' => $renderTemplateAction,
                'payum.converter.reply_to_http_response' => $replyToSymfonyResponseConverter,
                'payum.extension.update_payment_status' => $updatePaymentStatusExtension,
            ])->andReturnSelf()->once();
            $payumBuilderWrapper->shouldReceive('setHttpRequestVerifier')->andReturnSelf()->once();
            $payumBuilderWrapper->shouldReceive('setCoreGatewayFactory')->andReturnSelf()->once();
            $payumBuilderWrapper->shouldReceive('setGenericTokenFactoryPaths')->andReturnSelf()->once();
            $payumBuilderWrapper->shouldReceive('setGatewayConfig')->andReturnSelf()->once();

            $payumBuilderWrapper->shouldReceive('getBuilder')->andReturn(
                $payumBuilder = m::mock('Payum\Core\PayumBuilder')
            )->once();

            return $closure($app) instanceof PayumBuilder;
        }))->once();
        $app->shouldReceive('singleton')->with('Payum\Core\Payum', m::on(function ($closure) use ($app) {
            $app->shouldReceive('make')->with('Payum\Core\PayumBuilder')->andReturn($payumBuilder = m::mock('Payum\Core\PayumBuilder'));
            $payumBuilder->shouldReceive('getPayum')->andReturn($payum = m::mock('Payum\Core\Payum'))->once();

            return $closure($app) instanceof Payum;
        }))->once();
        $app->shouldReceive('singleton')->with('Recca0120\LaravelPayum\Service\PayumService', 'Recca0120\LaravelPayum\Service\PayumService')->once();
        $serviceProvider->register();
    }

    public function test_boot_service_provider()
    {
        $serviceProvider = new LaravelPayumServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $router = m::mock('Illuminate\Routing\Router');
        $app->shouldReceive('offsetGet')->with('config')->andReturn(['payum' => []]);
        $app->shouldReceive('routesAreCached')->andReturn(false);
        $router->shouldReceive('group')->with([
            'prefix' => 'payment',
            'as' => 'payment.',
            'namespace' => 'Recca0120\LaravelPayum\Http\Controllers',
            'middleware' => ['web'],
        ], m::type('Closure'))->once();
        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $viewFactory->shouldReceive('addNamespace')->with('payum', m::on(function($path) {
            return is_null(realpath($path)) === false;
        }))->once();
        $app->shouldReceive('runningInConsole')->andReturn(true)->once();
        $app->shouldReceive('configPath');
        $app->shouldReceive('basePath');
        $app->shouldReceive('databasePath');
        $serviceProvider->boot($router, $viewFactory);
        $this->assertSame([
            'Recca0120\LaravelPayum\PayumBuilderWrapper',
            'Payum\Core\PayumBuilder',
            'Payum\Core\Payum',
            'Recca0120\LaravelPayum\Service\PayumService',
        ], $serviceProvider->provides());
    }
}

function storage_path()
{
    return '';
}
