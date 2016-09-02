<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Payum;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;
use Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension;
use Recca0120\LaravelPayum\Model\GatewayConfig;
use Recca0120\LaravelPayum\Security\TokenFactory;
use Recca0120\LaravelPayum\Service\Payum as PayumService;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class ServiceProvider extends BaseServiceProvider
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
        $config = $this->app['config']->get('payum', []);
        $this->handleRoutes($router, $config)
            ->handlePublishes();
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
                'prefix'    => 'payment',
                'as'        => 'payment.',
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
        $this->registerPayumBuilder();
        $this->registerPayum();
        $this->app->singleton(PayumService::class, PayumService::class);
    }

    /**
     * registerPayumBuilder.
     *
     * @method registerPayumBuilder
     *
     * @return \Payum\Core\PayumBuilder
     */
    protected function registerPayumBuilder()
    {
        $this->app->bind('payum.converter.reply_to_http_response', ReplyToSymfonyResponseConverter::class);
        $this->app->bind('payum.action.get_http_request', GetHttpRequestAction::class);
        $this->app->bind('payum.action.obtain_credit_card', ObtainCreditCardAction::class);
        $this->app->bind('payum.action.render_template', RenderTemplateAction::class);
        $this->app->bind('payum.extension.update_payment_status', UpdatePaymentStatusExtension::class);

        return $this->app->singleton('payum.builder', function ($app) {
            $config = $app['config']->get('payum');
            $routeAlaisName = Arr::get($config, 'route.as');

            $builder = $app->make(PayumBuilder::class)
                ->setTokenFactory(function (StorageInterface $tokenStorage, StorageRegistryInterface $registry) use ($app) {
                    return $app->make(TokenFactory::class, [$tokenStorage, $registry]);
                })->setHttpRequestVerifier(function (StorageInterface $tokenStorage) use ($app) {
                    return $app->make(HttpRequestVerifier::class, [$tokenStorage]);
                })->setCoreGatewayFactory(function ($defaultConfig) use ($app) {
                    return $app->make(CoreGatewayFactory::class, [$app, $defaultConfig]);
                })->setCoreGatewayFactoryConfig([
                    'payum.action.obtain_credit_card'       => 'payum.action.obtain_credit_card',
                    'payum.action.render_template'          => 'payum.action.render_template',
                    'payum.extension.update_payment_status' => 'payum.extension.update_payment_status',
                ])->setGenericTokenFactoryPaths([
                    'authorize' => $routeAlaisName.'authorize',
                    'capture'   => $routeAlaisName.'capture',
                    'notify'    => $routeAlaisName.'notify',
                    'payout'    => $routeAlaisName.'payout',
                    'refund'    => $routeAlaisName.'refund',
                    'sync'      => $routeAlaisName.'sync',
                    'done'      => $routeAlaisName.'done',
                ]);

            $addStorages = (Arr::get($config, 'storage.token') === 'filesystem') ? 'addDefaultStorages' : 'addEloquentStorages';
            call_user_func([$builder, $addStorages]);

            $gatewayConfigs = Arr::get($config, 'gatewayConfigs', []);
            foreach ($gatewayConfigs as $factoryName => $config) {
                $factoryClass = Arr::get($config, 'factory');
                if (empty($factoryClass) === false && class_exists($factoryClass) === true) {
                    $builder
                        ->addGatewayFactory($factoryName, function ($config, GatewayFactoryInterface $coreGatewayFactory) use ($app, $factoryClass) {
                            return $app->make($factoryClass, [$config, $coreGatewayFactory]);
                        });
                }
                $config['factory'] = $factoryName;
                $builder->addGateway($factoryName, $config);
            }

            if (Arr::get($config, 'storage.gatewayConfig') === 'database') {
                $builder->setGatewayConfigStorage($app->make(EloquentStorage::class, [
                    'modelClass' => GatewayConfig::class,
                ]));
            }

            return $builder;
        });
    }

    /**
     * registerPayum.
     *
     * @method registerPayum
     *
     * @return \Payum\Core\Payum
     */
    protected function registerPayum()
    {
        return $this->app->singleton(Payum::class, function ($app) {
            return $this->app->make('payum.builder')->getPayum();
        });
    }
}
