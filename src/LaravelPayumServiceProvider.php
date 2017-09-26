<?php

namespace Recca0120\LaravelPayum;

use Closure;
use Payum\Core\Payum;
use Illuminate\Support\Arr;
use Payum\Core\Model\Token;
use Payum\Core\Model\Payout;
use Payum\Core\PayumBuilder;
use Payum\Core\Model\Payment;
use Illuminate\Routing\Router;
use Payum\Core\Model\ArrayObject;
use Payum\Core\CoreGatewayFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Storage\FilesystemStorage;
use Illuminate\Contracts\Routing\UrlGenerator;
use Payum\Core\Registry\StorageRegistryInterface;
use Recca0120\LaravelPayum\Security\TokenFactory;
use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
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
     * @param \Illuminate\Routing\Router $router
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
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/payum.php', 'payum');

        $this->app->singleton('payum.builder', function ($app) {
            $config = $app['config']['payum'];

            $routeAlias = Arr::get($config, 'route.as');
            $builder = (new PayumBuilder())
                ->setTokenFactory(function (StorageInterface $tokenStorage, StorageRegistryInterface $registry) use ($app) {
                    return new TokenFactory($tokenStorage, $registry, $app[UrlGenerator::class]);
                })
                ->setHttpRequestVerifier(function (StorageInterface $tokenStorage) {
                    return new HttpRequestVerifier($tokenStorage);
                })
                ->setCoreGatewayFactory(function ($defaultConfig) {
                    return new CoreGatewayFactory($defaultConfig);
                })
                ->setCoreGatewayFactoryConfig([
                    'payum.action.get_http_request' => $app[GetHttpRequestAction::class],
                    'payum.action.obtain_credit_card' => $app[ObtainCreditCardAction::class],
                    'payum.action.render_template' => $app[RenderTemplateAction::class],
                    'payum.converter.reply_to_http_response' => $app[ReplyToSymfonyResponseConverter::class],
                    'payum.extension.update_payment_status' => $app[UpdatePaymentStatusExtension::class],
                ])
                ->setGenericTokenFactoryPaths([
                    'authorize' => $routeAlias.'authorize',
                    'capture' => $routeAlias.'capture',
                    'notify' => $routeAlias.'notify',
                    'payout' => $routeAlias.'payout',
                    'refund' => $routeAlias.'refund',
                    'cancel' => $routeAlias.'cancel',
                    'sync' => $routeAlias.'sync',
                    'done' => $routeAlias.'done',
                ]);

            $this->setStorage($builder, $app[Filesystem::class], $config);
            $this->setGatewayConfigs($builder, $config['drivers']);

            return $builder;
        });

        $this->app->singleton(Payum::class, function ($app) {
            return $app['payum.builder']->getPayum();
        });

        $this->app->alias(Payum::class, 'payum');

        $this->app->singleton(PayumManager::class, function ($app) {
            return new PayumManager($app);
        });
    }

    /**
     * provides.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['payum.builder', 'payum'];
    }

    /**
     * setStorage.
     *
     * @param \Payum\Core\PayumBuilder $builder
     * @param \Illuminate\Filesystem\Filesystem   $files
     * @param array $config
     */
    protected function setStorage(PayumBuilder $builder, Filesystem $files, $config)
    {
        $storagePath = $config['storage']['path'];
        if ($files->isDirectory($storagePath) === false) {
            $files->makeDirectory($storagePath, 0777, true);
        }

        if ($config['storage']['token'] === 'files') {
            $builder->setTokenStorage(new FilesystemStorage($storagePath, Token::class, 'hash'));
        } else {
            $builder->setTokenStorage(new EloquentStorage(EloquentToken::class))
                ->addStorage(EloquentPayment::class, new EloquentStorage(EloquentPayment::class));
        }

        return $builder
            ->addStorage(Payment::class, new FilesystemStorage($storagePath, Payment::class, 'number'))
            ->addStorage(ArrayObject::class, new FilesystemStorage($storagePath, ArrayObject::class))
            ->addStorage(Payout::class, new FilesystemStorage($storagePath, Payout::class));
    }

    /**
     * setGatewayConfigs.
     *
     * @param \Payum\Core\PayumBuilder $builder
     * @param array $drivers
     */
    protected function setGatewayConfigs(PayumBuilder $builder, $drivers)
    {
        foreach ($drivers as $name => $config) {
            $this->setGateway($builder, $name, $config);
        }

        return $builder;
    }

    /**
     * setGateway.
     *
     * @param \Payum\Core\PayumBuilder $builder
     * @param string $name
     * @param array $config
     */
    protected function setGateway(PayumBuilder $builder, $name, $config)
    {
        $factory = $config['factory'];
        if (($factory instanceof Closure) === false && class_exists($factory) === true) {
            $factory = function ($config, GatewayFactoryInterface $coreGatewayFactory) use ($factory) {
                return new $factory($config, $coreGatewayFactory);
            };
            $builder->addGatewayFactory($name, $factory);
            $config['factory'] = $name;
        }

        return $builder->addGateway($name, $config);
    }

    /**
     * register routes.
     *
     * @param \Illuminate\Routing\Router $router
     * @param array $config
     * @return $this
     */
    protected function handleRoutes(Router $router, $config = [])
    {
        if ($this->app->routesAreCached() === false) {
            $router->group(array_merge([
                'prefix' => 'payum',
                'as' => 'payum.',
                'namespace' => $this->namespace,
                'middleware' => ['web'],
            ], Arr::get($config, 'route', [])), function (Router $router) {
                require __DIR__.'/../routes/web.php';
            });
        }

        return $this;
    }

    /**
     * handle publishes.
     *
     * @return $this
     */
    protected function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../config/payum.php' => config_path('payum.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/payum'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'public');

        return $this;
    }
}
