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

    public function setTokenFactory()
    {
        $this->builder->setTokenFactory(function (StorageInterface $tokenStorage, StorageRegistryInterface $registry) {
            return $this->app->make(TokenFactory::class, [$tokenStorage, $registry]);
        });
    }

    public function setHttpRequestVerifier()
    {
        $this->builder->setHttpRequestVerifier(function (StorageInterface $tokenStorage) {
            return $this->app->make(HttpRequestVerifier::class, [$tokenStorage]);
        });
    }

    public function setCoreGatewayFactory()
    {
        $this->builder->setCoreGatewayFactory(function ($defaultConfig) {
            return $this->app->make(CoreGatewayFactory::class, [$this->app, $defaultConfig]);
        });
    }

    public function setCoreGatewayFactoryConfig()
    {
        $this->builder->setCoreGatewayFactoryConfig([
            'payum.action.obtain_credit_card' => 'payum.action.obtain_credit_card',
            'payum.action.render_template' => 'payum.action.render_template',
            'payum.extension.update_payment_status' => 'payum.extension.update_payment_status',
        ]);
    }

    public function setGenericTokenFactoryPaths()
    {
        $routeAlaisName = array_get($this->config, 'route.as');

        $this->builder->setGenericTokenFactoryPaths([
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

    public function setStorage()
    {
        (array_get($this->config, 'storage.token') === 'eloquent') ?
            $this->builder->addEloquentStorages() : $this->builder->addDefaultStorages();
    }

    public function setGatewayConfig()
    {
        $gatewayConfigs = array_get($this->config, 'gatewayConfigs', []);

        if (array_get($this->config, 'storage.gatewayConfig') === 'eloquent') {
            $gatewayConfigStorage = $this->app->make(EloquentStorage::class, [
                'modelClass' => GatewayConfig::class,
            ]);
            $this->builder->setGatewayConfigStorage($gatewayConfigStorage);

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
                $this->builder
                    ->addGatewayFactory($gatewayName, function ($gatewayConfig, GatewayFactoryInterface $coreGatewayFactory) use ($factoryName) {
                        return $this->app->make($factoryName, [$gatewayConfig, $coreGatewayFactory]);
                    });
            }
            $gatewayConfig['factory'] = $gatewayName;
            $this->builder->addGateway($gatewayName, $gatewayConfig);
        }
    }

    public function getBuilder()
    {
        $this->setTokenFactory();
        $this->setHttpRequestVerifier();
        $this->setCoreGatewayFactory();
        $this->setCoreGatewayFactoryConfig();
        $this->setGenericTokenFactoryPaths();
        $this->setStorage();
        $this->setGatewayConfig();

        return $this->builder;
    }
}
