<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Model\Token as PayumToken;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Model\GatewayConfig;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Recca0120\LaravelPayum\Security\TokenFactory;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class PayumBuilderManager
{
    /**
     * $payumBuilder.
     *
     * @var \Payum\Core\PayumBuilder
     */
    protected $payumBuilder;

    /**
     * $filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * $app.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * $routeAliasName.
     *
     * @var string
     */
    protected $routeAliasName;

    /**
     * $tokenStorageType.
     *
     * @var string
     */
    protected $tokenStorageType;

    /**
     * $gatewayConfigStorageType.
     *
     * @var string
     */
    protected $gatewayConfigStorageType;

    /**
     * $storagePath.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * $gatewayConfigs.
     *
     * @var array
     */
    protected $gatewayConfigs;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Payum\Core\PayumBuilder                     $payumBuilder
     * @param \Illuminate\Filesystem\Filesystem            $filesystem
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param array                                        $config
     */
    public function __construct(PayumBuilder $payumBuilder, Filesystem $filesystem, Application $app, $config = [])
    {
        $this->payumBuilder = $payumBuilder;
        $this->filesystem = $filesystem;
        $this->app = $app;

        $this->tokenStorageType = array_get($config, 'storage.token', 'filesystem');
        $this->gatewayConfigStorageType = array_get($config, 'storage.gatewayConfig', 'filesystem');

        $this->routeAliasName = array_get($config, 'route.as');
        $this->storagePath = array_get($config, 'path');
        $this->gatewayConfigs = array_get($config, 'gatewayConfigs', []);
    }

    /**
     * createTokenFactory.
     * @method createTokenFactory
     *
     * @param \Payum\Core\Storage\StorageInterface             $tokenStorage
     * @param \Payum\Core\Registry\StorageRegistryInterface    $registry
     *
     * @return \Recca0120\LaravelPayum\Security\TokenFactory
     */
    protected function createTokenFactory(StorageInterface $tokenStorage, StorageRegistryInterface $registry)
    {
        return $this->app->make(TokenFactory::class, [$tokenStorage, $registry]);
    }

    /**
     * createHttpRequestVerifier.
     *
     * @method createHttpRequestVerifier
     *
     * @param \Payum\Core\Storage\StorageInterface  $tokenStorage
     *
     * @return \Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier
     */
    protected function createHttpRequestVerifier(StorageInterface $tokenStorage)
    {
        return $this->app->make(HttpRequestVerifier::class, [$tokenStorage]);
    }

    /**
     * createCoreGatewayFactoryConfig.
     *
     * @method createCoreGatewayFactoryConfig
     *
     * @param array     $defaultConfig
     *
     * @return \Recca0120\LaravelPayum\CoreGatewayFactory
     */
    protected function createCoreGatewayFactoryConfig($defaultConfig)
    {
        return $this->app->make(CoreGatewayFactory::class, [
            'defaultConfig' => $defaultConfig,
        ]);
    }

    /**
     * createGenericTokenFactoryPaths.
     *
     * @method createGenericTokenFactoryPaths
     *
     * @return array
     */
    protected function createGenericTokenFactoryPaths()
    {
        return [
            'authorize' => $this->routeAliasName.'authorize',
            'capture' => $this->routeAliasName.'capture',
            'notify' => $this->routeAliasName.'notify',
            'payout' => $this->routeAliasName.'payout',
            'refund' => $this->routeAliasName.'refund',
            'cancel' => $this->routeAliasName.'cancel',
            'sync' => $this->routeAliasName.'sync',
            'done' => $this->routeAliasName.'done',
        ];
    }

    /**
     * createEloquentStorage.
     *
     * @method createEloquentStorage
     *
     * @param string   $modelClass
     *
     * @return \Recca0120\LaravelPayum\Storage\EloquentStorage
     */
    protected function createEloquentStorage($modelClass)
    {
        return $this->app->make(EloquentStorage::class, [
            $modelClass,
            $this->app,
        ]);
    }

    /**
     * createFilesystemStorage.
     *
     * @method createFilesystemStorage
     *
     * @param string    $modelClass
     * @param string    $idProperty
     *
     * @return \Payum\Core\Storage\FilesystemStorage
     */
    protected function createFilesystemStorage($modelClass, $idProperty = 'payum_id')
    {
        return $this->app->make(FilesystemStorage::class, [
            $this->storagePath,
            $modelClass,
            $idProperty,
        ]);
    }

    /**
     * setTokenFactory.
     *
     * @method setTokenFactory
     *
     * @return self
     */
    protected function setTokenFactory()
    {
        $this->payumBuilder->setTokenFactory([$this, 'createTokenFactory']);

        return $this;
    }

    /**
     * setHttpRequestVerifier.
     *
     * @method setHttpRequestVerifier
     *
     * @return self
     */
    protected function setHttpRequestVerifier()
    {
        $this->payumBuilder->setHttpRequestVerifier([$this, 'createHttpRequestVerifier']);

        return $this;
    }

    /**
     * setCoreGatewayFactory.
     *
     * @method setCoreGatewayFactory
     *
     * @return self
     */
    protected function setCoreGatewayFactory()
    {
        $this->payumBuilder->setCoreGatewayFactory([$this, 'createCoreGatewayFactoryConfig']);

        return $this;
    }

    /**
     * setGenericTokenFactoryPaths.
     *
     * @method setGenericTokenFactoryPaths
     *
     * @return self
     */
    protected function setGenericTokenFactoryPaths()
    {
        $this->payumBuilder->setGenericTokenFactoryPaths($this->createGenericTokenFactoryPaths());

        return $this;
    }

    /**
     * setStorage.
     *
     * @method setStorage
     *
     * @return self
     */
    protected function setStorage()
    {
        return ($this->tokenStorageType === 'eloquent') ?
            $this->setEloquentStorage() : $this->setFilesystemStorage();
    }

    /**
     * setEloquentStorage.
     *
     * @method setEloquentStorage
     *
     * @return self
     */
    protected function setEloquentStorage()
    {
        $this->payumBuilder
            ->setTokenStorage($this->createEloquentStorage(EloquentToken::class))
            ->addStorage(EloquentPayment::class, $this->createEloquentStorage(EloquentPayment::class));

        return $this;
    }

    /**
     * setFilesystemStorage.
     *
     * @method setFilesystemStorage
     *
     * @return self
     */
    protected function setFilesystemStorage()
    {
        if ($this->filesystem->isDirectory($this->storagePath) === false) {
            $this->filesystem->makeDirectory($this->storagePath, 0777, true);
        }

        $this->payumBuilder
            ->setTokenStorage($this->createFilesystemStorage(PayumToken::class, 'hash'))
            ->addStorage(PayumPayment::class, $this->createFilesystemStorage(PayumPayment::class, 'number'))
            ->addStorage(ArrayObject::class, $this->createFilesystemStorage(ArrayObject::class));

        return $this;
    }

    /**
     * setGatewayConfigStorage.
     *
     * @method setGatewayConfigStorage
     *
     * @return self
     */
    protected function setGatewayConfigStorage()
    {
        if ($this->gatewayConfigStorageType === 'eloquent') {
            $storage = $this->createEloquentStorage(GatewayConfig::class);

            $this->payumBuilder->setGatewayConfigStorage($storage);

            foreach ($storage->findBy([]) as $gatewayConfig) {
                $gatewayName = $gatewayConfig->getGatewayName();
                $factoryName = $gatewayConfig->getFactoryName();
                $this->gatewayConfigs[$gatewayName] = array_merge(
                    array_get($this->gatewayConfigs, $gatewayName, []),
                    ['factory' => $factoryName],
                    $gatewayConfig->getConfig()
                );
            }
        }

        return $this;
    }

    /**
     * getGatewayConfigs.
     *
     * @method getGatewayConfigs
     *
     * @return array
     */
    protected function getGatewayConfigs()
    {
        return $this->gatewayConfigs;
    }

    /**
     * setGatewayConfig.
     *
     * @method setGatewayConfig
     *
     * @return self
     */
    protected function setGatewayConfig()
    {
        foreach ($this->gatewayConfigs as $gatewayName => $gatewayConfig) {
            $factoryName = array_get($gatewayConfig, 'factory');
            if (empty($factoryName) === false && class_exists($factoryName) === true) {
                $this->payumBuilder
                    ->addGatewayFactory($gatewayName, function ($gatewayConfig, GatewayFactoryInterface $coreGatewayFactory) use ($factoryName) {
                        return $this->app->make($factoryName, [$gatewayConfig, $coreGatewayFactory]);
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
     * @method getBuilder
     *
     * @return \Payum\Core\PayumBuilder
     */
    public function getBuilder()
    {
        $this
            ->setTokenFactory()
            ->setHttpRequestVerifier()
            ->setCoreGatewayFactory()
            ->setGenericTokenFactoryPaths()
            ->setStorage()
            ->setGatewayConfigStorage()
            ->setGatewayConfig();

        return $this->payumBuilder;
    }
}
