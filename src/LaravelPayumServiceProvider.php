<?php

namespace Recca0120\LaravelPayum;

use Payum\Core\Payum;
use Illuminate\Support\Arr;
use Payum\Core\PayumBuilder;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Recca0120\LaravelPayum\Service\PayumService;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension;

class LaravelPayumServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Recca0120\LaravelPayum\Http\Controllers';

    /**
     * boot.
     *
     * @method boot
     *
     * @param \Illuminate\Routing\Router         $router
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     */
    public function boot(Router $router, ViewFactory $viewFactory)
    {
        $viewFactory->addNamespace('payum', __DIR__.'/../resources/views');
        $this->handleRoutes($router, $this->app['config']['payum']);
        if ($this->app->runningInConsole() === true) {
            $this->handlePublishes();
        }
    }

    /**
     * register routes.
     *
     * @param \Illuminate\Routing\Router $router
     * @param array                     $config
     *
     * @return static
     */
    protected function handleRoutes(Router $router, $config = [])
    {
        if ($this->app->routesAreCached() === false) {
            $router->group(array_merge([
                'prefix' => 'payment',
                'as' => 'payment.',
                'namespace' => $this->namespace,
                'middleware' => ['web'],
            ], Arr::get($config, 'route', [])), function (Router $router) {
                require __DIR__.'/Http/routes.php';
            });
        }

        return $this;
    }

    /**
     * handle publishes.
     *
     * @return static
     */
    protected function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../config/payum.php' => $this->app->configPath().'/payum.php',
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => $this->app->basePath().'/resources/views/vendor/payum/',
        ], 'views');

        $this->publishes([
            __DIR__.'/../database/migrations' => $this->app->databasePath().'/migrations/',
        ], 'public');

        return $this;
    }

    /**
     * Register the service provider.
     *
     * @method register
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/payum.php', 'payum');

        $this->app->singleton(PayumBuilderWrapper::class, function ($app) {
            return new PayumBuilderWrapper(new PayumBuilder, $app['config']['payum']);
        });

        $this->app->singleton(PayumBuilder::class, function ($app) {
            return $app->make(PayumBuilderWrapper::class)
                ->setTokenFactory($app['url'])
                ->setStorage($app['files'])
                ->setCoreGatewayFactoryConfig([
                    'payum.action.get_http_request' => $app->make(GetHttpRequestAction::class),
                    'payum.action.obtain_credit_card' => $app->make(ObtainCreditCardAction::class),
                    'payum.action.render_template' => $app->make(RenderTemplateAction::class),
                    'payum.converter.reply_to_http_response' => $app->make(ReplyToSymfonyResponseConverter::class),
                    'payum.extension.update_payment_status' => $app->make(UpdatePaymentStatusExtension::class),
                ])
                ->setHttpRequestVerifier()
                ->setCoreGatewayFactory()
                ->setGenericTokenFactoryPaths()
                ->setGatewayConfig()
                ->getBuilder();
        });

        $this->app->singleton(Payum::class, function ($app) {
            return $this->app->make(PayumBuilder::class)->getPayum();
        });

        $this->app->singleton(PayumService::class, PayumService::class);
    }

    /**
     * provides.
     *
     * @method provides
     *
     * @return array
     */
    public function provides()
    {
        return [
            PayumBuilderWrapper::class,
            PayumBuilder::class,
            Payum::class,
            PayumService::class,
        ];
    }
}
