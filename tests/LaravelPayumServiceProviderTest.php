<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\PayumBuilderWrapper;
use Recca0120\LaravelPayum\LaravelPayumServiceProvider;

class LaravelPayumServiceProviderTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testRegisterServiceProvider()
    {
        $serviceProvider = new LaravelPayumServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $app->shouldReceive('offsetGet')->twice()->with('config')->andReturn($config = m::mock('stdClass'));
        $config->shouldReceive('get')->with('payum', [])->andReturn([]);
        $config->shouldReceive('set')->with('payum', m::type('array'))->andReturn([]);
        $app->shouldReceive('singleton')->once()->with('Recca0120\LaravelPayum\PayumBuilderWrapper', m::on(function ($closure) use ($app) {
            $app->shouldReceive('offsetGet')->once()->with('config')->andReturn(['payum' => ['path' => 'foo']]);

            return $closure($app) instanceof PayumBuilderWrapper;
        }));
        $app->shouldReceive('singleton')->once()->with('Payum\Core\PayumBuilder', m::on(function ($closure) use ($app) {
            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\Action\GetHttpRequestAction')->andReturn($getHttpRequestAction = 'GetHttpRequestAction');
            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\Action\ObtainCreditCardAction')->andReturn($obtainCreditCardAction = 'ObtainCreditCardAction');
            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\Action\RenderTemplateAction')->andReturn($renderTemplateAction = 'RenderTemplateAction');
            $app->shouldReceive('make')->once()->with('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')->andReturn($replyToSymfonyResponseConverter = 'ReplyToSymfonyResponseConverter');
            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension')->andReturn($updatePaymentStatusExtension = 'UpdatePaymentStatusExtension');

            $app->shouldReceive('offsetGet')->once()->with('url')->andReturn(
                $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
            );
            $app->shouldReceive('offsetGet')->once()->with('files')->andReturn(
                $files = m::mock('Illuminate\Filesystem\Filesystem')
            );

            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\PayumBuilderWrapper')->andReturn(
                $payumBuilderWrapper = m::mock('Recca0120\LaravelPayum\PayumBuilderWrapper')
            );
            $payumBuilderWrapper->shouldReceive('setTokenFactory')->once()->with($urlGenerator)->andReturnSelf();
            $payumBuilderWrapper->shouldReceive('setStorage')->once()->with($files)->andReturnSelf();
            $payumBuilderWrapper->shouldReceive('setCoreGatewayFactoryConfig')->once()->with([
                'payum.action.get_http_request' => $getHttpRequestAction,
                'payum.action.obtain_credit_card' => $obtainCreditCardAction,
                'payum.action.render_template' => $renderTemplateAction,
                'payum.converter.reply_to_http_response' => $replyToSymfonyResponseConverter,
                'payum.extension.update_payment_status' => $updatePaymentStatusExtension,
            ])->andReturnSelf();
            $payumBuilderWrapper->shouldReceive('setHttpRequestVerifier')->once()->andReturnSelf();
            $payumBuilderWrapper->shouldReceive('setCoreGatewayFactory')->once()->andReturnSelf();
            $payumBuilderWrapper->shouldReceive('setGenericTokenFactoryPaths')->once()->andReturnSelf();
            $payumBuilderWrapper->shouldReceive('setGatewayConfig')->once()->andReturnSelf();

            $payumBuilderWrapper->shouldReceive('getBuilder')->once()->andReturn(
                $payumBuilder = m::mock('Payum\Core\PayumBuilder')
            );

            return $closure($app) instanceof PayumBuilder;
        }));
        $app->shouldReceive('singleton')->once()->with('Payum\Core\Payum', m::on(function ($closure) use ($app) {
            $app->shouldReceive('make')->with('Payum\Core\PayumBuilder')->andReturn($payumBuilder = m::mock('Payum\Core\PayumBuilder'));
            $payumBuilder->shouldReceive('getPayum')->once()->andReturn($payum = m::mock('Payum\Core\Payum'));

            return $closure($app) instanceof Payum;
        }));
        $app->shouldReceive('singleton')->once()->with('Recca0120\LaravelPayum\Service\PayumService', 'Recca0120\LaravelPayum\Service\PayumService');
        $serviceProvider->register();
    }

    public function testBootServiceProvider()
    {
        $serviceProvider = new LaravelPayumServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $router = m::mock('Illuminate\Routing\Router');
        $app->shouldReceive('offsetGet')->with('config')->andReturn(['payum' => []]);
        $app->shouldReceive('routesAreCached')->andReturn(false);
        $router->shouldReceive('group')->once()->with([
            'prefix' => 'payment',
            'as' => 'payment.',
            'namespace' => 'Recca0120\LaravelPayum\Http\Controllers',
            'middleware' => ['web'],
        ], m::type('Closure'));
        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $viewFactory->shouldReceive('addNamespace')->once()->with('payum', m::on(function ($path) {
            return is_null(realpath($path)) === false;
        }));
        $app->shouldReceive('runningInConsole')->once()->andReturn(true);
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
