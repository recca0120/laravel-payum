<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Payum;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;
use Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension;
use Recca0120\LaravelPayum\Service\PayumService;

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
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     * @param \Illuminate\Routing\Router         $router
     */
    public function boot(ViewFactory $viewFactory, Router $router)
    {
        $viewFactory->addNamespace('payum', __DIR__.'/../resources/views');
        $config = $this->app['config']['payum'];
        $this->handleRoutes($router, $config);

        if ($this->app->runningInConsole() === true) {
            $this->handlePublishes();
        }
    }

    /**
     * register routes.
     *
     * @param Illuminate\Routing\Router $router
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
            __DIR__.'/../database/migrations' => $this->app->basePath().'/database/migrations/',
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

        $this->app->bind('payum.converter.reply_to_http_response', ReplyToSymfonyResponseConverter::class);
        $this->app->bind('payum.action.get_http_request', GetHttpRequestAction::class);
        $this->app->bind('payum.action.obtain_credit_card', ObtainCreditCardAction::class);
        $this->app->bind('payum.action.render_template', RenderTemplateAction::class);
        $this->app->bind('payum.extension.update_payment_status', UpdatePaymentStatusExtension::class);

        $this->app->singleton('payum.builder', function ($app) {
            $config = $app['config']['payum'];

            return $app->make(PayumBuilderManager::class, [
                'config' => $config,
            ])->getBuilder();
        });

        $this->app->singleton(Payum::class, function ($app) {
            return $this->app->make('payum.builder')->getPayum();
        });

        $this->app->singleton(PayumService::class, PayumService::class);

        class_alias(PayumService::class, 'Recca0120\LaravelPayum\Service\Payum');
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
        return ['payum', 'payum.builder', 'payum.converter.reply_to_http_response'];
    }
}
