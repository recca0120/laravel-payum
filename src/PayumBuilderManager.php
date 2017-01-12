<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Support\Arr;
use Payum\Core\PayumBuilder;
use Payum\Core\Model\ArrayObject;
use Payum\Core\CoreGatewayFactory;
use Illuminate\Filesystem\Filesystem;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Model\Token as PayumToken;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Model\Payment as PayumPayment;
use Illuminate\Contracts\Routing\UrlGenerator;
use Recca0120\LaravelPayum\Model\GatewayConfig;
use Illuminate\Contracts\Foundation\Application;
use Payum\Core\Registry\StorageRegistryInterface;
use Recca0120\LaravelPayum\Security\TokenFactory;
use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;

class PayumBuilderManager
{
    /**
     * $payumBuilder.
     *
     * @var \Payum\Core\PayumBuilder
     */
    protected $payumBuilder;

    /**
     * $config.
     *
     * @var array
     */
    protected $config;

    /**
     * $app.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * __construct.
     *
     * @param \Payum\Core\PayumBuilder $payumBuilder
     * @param array $config
     * @param \Illuminate\Contracts\Foundation\Application  $app
     */
    public function __construct(PayumBuilder $payumBuilder, $config = [], Application $app = null)
    {
        $this->payumBuilder = $payumBuilder;
        $this->config = $config;
        $this->app = $app;
    }

    /**
     * setTokenFactory.
     *
     * @param \Illuminate\Contracts\Routing\UrlGenerator $urlGenerator
     *
     * @return static
     */
    public function setTokenFactory(UrlGenerator $urlGenerator)
    {
        $this->payumBuilder->setTokenFactory(function (StorageInterface $tokenStorage, StorageRegistryInterface $registry) use ($urlGenerator) {
            return new TokenFactory($tokenStorage, $registry, $urlGenerator);
        });

        return $this;
    }

    /**
     * setHttpRequestVerifier.
     *
     * @return static
     */
    public function setHttpRequestVerifier()
    {
        $this->payumBuilder->setHttpRequestVerifier(function (StorageInterface $tokenStorage) {
            return new HttpRequestVerifier($tokenStorage);
        });

        return $this;
    }

    /**
     * setCoreGatewayFactory.
     *
     * @return static
     */
    public function setCoreGatewayFactory()
    {
        $this->payumBuilder->setCoreGatewayFactory(function ($defaultConfig) {
            return new CoreGatewayFactory($defaultConfig);
        });

        return $this;
    }

    /**
     * setCoreGatewayFactoryConfig.
     *
     * @param array $config
     *
     * @return static
     */
    public function setCoreGatewayFactoryConfig($config)
    {
        $this->payumBuilder->setCoreGatewayFactoryConfig($config);

        return $this;
    }

    /**
     * setGenericTokenFactoryPaths.
     *
     * @return static
     */
    public function setGenericTokenFactoryPaths()
    {
        $routeAliasName = Arr::get($this->config, 'route.as');
        $this->payumBuilder->setGenericTokenFactoryPaths([
            'authorize' => $routeAliasName.'authorize',
            'capture' => $routeAliasName.'capture',
            'notify' => $routeAliasName.'notify',
            'payout' => $routeAliasName.'payout',
            'refund' => $routeAliasName.'refund',
            'cancel' => $routeAliasName.'cancel',
            'sync' => $routeAliasName.'sync',
            'done' => $routeAliasName.'done',
        ]);

        return $this;
    }

    /**
     * setStorage.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     *
     * @return static
     */
    public function setStorage(Filesystem $filesystem)
    {
        return Arr::get($this->config, 'storage.token') === 'eloquent' ?
            $this->setEloquentStorage() : $this->setFilesystemStorage($filesystem);
    }

    /**
     * setEloquentStorage.
     *
     * @method setEloquentStorage
     *
     * @return static
     */
    public function setEloquentStorage()
    {
        $this->payumBuilder
            ->setTokenStorage(new EloquentStorage(EloquentToken::class, $this->app))
            ->addStorage(EloquentPayment::class, new EloquentStorage(EloquentPayment::class, $this->app));

        return $this;
    }

    /**
     * setFilesystemStorage.
     *
     * @method setFilesystemStorage
     *
     * @return static
     */
    public function setFilesystemStorage(Filesystem $filesystem)
    {
        $storagePath = Arr::get($this->config, 'path');
        if ($filesystem->isDirectory($storagePath) === false) {
            $filesystem->makeDirectory($storagePath, 0777, true);
        }

        $this->payumBuilder
            ->setTokenStorage(new FilesystemStorage($storagePath, PayumToken::class, 'hash'))
            ->addStorage(PayumPayment::class, new FilesystemStorage($storagePath, PayumPayment::class, 'number'))
            ->addStorage(ArrayObject::class, new FilesystemStorage($storagePath, ArrayObject::class));

        return $this;
    }

    /**
     * loadGatewayConfigs.
     *
     * @return array
     */
    public function loadGatewayConfigs()
    {
        $gatewayConfigs = [];
        $storage = new EloquentStorage(GatewayConfig::class, $this->app);

        $this->payumBuilder->setGatewayConfigStorage($storage);

        foreach ($storage->findBy([]) as $gatewayConfig) {
            $gatewayName = $gatewayConfig->getGatewayName();
            $factoryName = $gatewayConfig->getFactoryName();
            $gatewayConfigs[$gatewayName] = array_merge(
                Arr::get($gatewayConfigs, $gatewayName, []),
                ['factory' => $factoryName],
                $gatewayConfig->getConfig()
            );
        }

        return $gatewayConfigs;
    }

    /**
     * setGatewayConfig.
     *
     * @method setGatewayConfig
     *
     * @return self
     */
    public function setGatewayConfig()
    {
        $gatewayConfigs = Arr::get($this->config, 'gatewayConfigs', []);
        if (Arr::get($this->config, 'storage.gatewayConfig') === 'eloquent') {
            $gatewayConfigs = array_merge($gatewayConfigs, $this->loadGatewayConfigs());
        }
        foreach ($gatewayConfigs as $gatewayName => $gatewayConfig) {
            $factoryName = Arr::get($gatewayConfig, 'factory');
            if (empty($factoryName) === false && class_exists($factoryName) === true) {
                $this->payumBuilder
                    ->addGatewayFactory($gatewayName, function ($gatewayConfig, GatewayFactoryInterface $coreGatewayFactory) use ($factoryName) {
                        return is_null($this->app) === true ?
                            new $factoryName($gatewayConfig, $coreGatewayFactory) :
                            $this->app->make($factoryName, [$gatewayConfig, $coreGatewayFactory]);
                    });
            }
            $gatewayConfig['factory'] = $gatewayName;
            $this->payumBuilder->addGateway($gatewayName, $gatewayConfig);
        }

        return $this;
    }

    /**
     * getBuilder.
     *
     * @return \Payum\Core\PayumBuilder
     */
    public function getBuilder()
    {
        return $this
            ->setHttpRequestVerifier()
            ->setCoreGatewayFactory()
            ->setGenericTokenFactoryPaths()
            ->setGatewayConfig()
            ->payumBuilder;
    }
}
