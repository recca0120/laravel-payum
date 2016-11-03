<?php

namespace Recca0120\LaravelPayum;

use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Payum;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Model\GatewayConfig;
use Recca0120\LaravelPayum\Security\TokenFactory;

class PayumBuilderAdapter
{
    public function __construct($builder, $app, $config)
    {
        $this->builder = $builder;
        $this->app = $app;
        $this->config = $config;
    }

    public function tokenFactory(StorageInterface $tokenStorage, StorageRegistryInterface $registry) {
        return $this->app->make(TokenFactory::class, [$tokenStorage, $registry]);
    }

    public function setTokenFactory($builder)
    {
        $builder->setTokenFactory([$this, 'tokenFactory']);
    }

    public function httpRequestVerifier(StorageInterface $tokenStorage) {
        return $this->app->make(HttpRequestVerifier::class, [$tokenStorage]);
    }

    public function setHttpRequestVerifier($bulder)
    {
        $bulder->setHttpRequestVerifier([$this, 'httpRequestVerifier']);
    }

    public function setCoreGatewayFactory($bulder)
    {
        $bulder->setCoreGatewayFactory(function ($defaultConfig) {
            return $this->app->make(CoreGatewayFactory::class, [$this->app, $defaultConfig]);
        });
    }

    public function setCoreGatewayFactoryConfig($bulder)
    {
        $bulder->setCoreGatewayFactoryConfig([
            'payum.action.obtain_credit_card' => 'payum.action.obtain_credit_card',
            'payum.action.render_template' => 'payum.action.render_template',
            'payum.extension.update_payment_status' => 'payum.extension.update_payment_status',
        ]);
    }

    public function setGenericTokenFactoryPaths($bulder)
    {
        $routeAlaisName = array_get($this->config, 'route.as');

        $bulder->setGenericTokenFactoryPaths([
            'authorize' => $routeAlaisName.'authorize',
            'capture' => $routeAlaisName.'capture',
            'notify' => $routeAlaisName.'notify',
            'payout' => $routeAlaisName.'payout',
            'refund' => $routeAlaisName.'refund',
            'cancel' => $routeAlaisName.'cancel',
            'sync' => $routeAlaisName.'sync',
            'done' => $routeAlaisName.'done',
        ]);
    }

    public function setStorage($bulder)
    {
        (array_get($this->config, 'storage.token') === 'eloquent') ?
            $bulder->addEloquentStorages() : $bulder->addDefaultStorages();
    }

    public function setGatewayConfig($bulder)
    {
        $gatewayConfigs = array_get($this->config, 'gatewayConfigs', []);

        if (array_get($this->config, 'storage.gatewayConfig') === 'eloquent') {
            $gatewayConfigStorage = $this->app->make(EloquentStorage::class, [
                'modelClass' => GatewayConfig::class,
            ]);
            $bulder->setGatewayConfigStorage($gatewayConfigStorage);

            foreach ($gatewayConfigStorage->findBy([]) as $eloquentGatewayConfig) {
                $gatewayName = $eloquentGatewayConfig->getGatewayName();
                $factoryName = $eloquentGatewayConfig->getFactoryName();
                $gatewayConfigs[$gatewayName] = array_merge(
                    array_get($gatewayConfigs, $gatewayName, []),
                    ['factory' => $factoryName],
                    $eloquentGatewayConfig->getConfig()
                );
            }
        }

        foreach ($gatewayConfigs as $gatewayName => $gatewayConfig) {
            $factoryName = array_get($gatewayConfig, 'factory');
            if (empty($factoryName) === false && class_exists($factoryName) === true) {
                $bulder
                    ->addGatewayFactory($gatewayName, function ($gatewayConfig, GatewayFactoryInterface $coreGatewayFactory) use ($factoryName) {
                        return $this->app->make($factoryName, [$gatewayConfig, $coreGatewayFactory]);
                    });
            }
            $gatewayConfig['factory'] = $gatewayName;
            $bulder->addGateway($gatewayName, $gatewayConfig);
        }
    }

    public function getBuilder()
    {
        $this->setTokenFactory($this->builder);
        $this->setHttpRequestVerifier($this->builder);
        $this->setCoreGatewayFactory($this->builder);
        $this->setCoreGatewayFactoryConfig($this->builder);
        $this->setGenericTokenFactoryPaths($this->builder);
        $this->setStorage($this->builder);
        $this->setGatewayConfig($this->builder);

        return $this->builder;
    }
}
