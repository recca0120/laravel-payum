<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Model\Token as PayumToken;
use Payum\Core\PayumBuilder as CorePayumBuilder;
use Payum\Core\Storage\FilesystemStorage;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class PayumBuilder extends CorePayumBuilder
{
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
     * $config.
     *
     * @var array
     */
    protected $config;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Contracts\Config\Repository
     */
    public function __construct(Filesystem $filesystem, $app, $config = [])
    {
        $this->filesystem = $filesystem;
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * addDefaultStorages.
     *
     * @method addDefaultStorages
     *
     * @return \Recca0120\LaravelPayum\PayumBuilder
     */
    public function addDefaultStorages()
    {
        $path = Arr::get($this->config, 'path');
        if ($this->filesystem->isDirectory($path) === false) {
            $this->filesystem->makeDirectory($path, 0777, true);
        }

        $this->setTokenStorage($this->app->make(FilesystemStorage::class, [
                'path' => $path,
                'modelClass' => PayumToken::class,
                'idProperty' => 'hash',
            ]))
            ->addStorage(PayumPayment::class, $this->app->make(FilesystemStorage::class, [
                'path' => $path,
                'modelClass' => PayumPayment::class,
                'idProperty' => 'number',
            ]))
            ->addStorage(ArrayObject::class, $this->app->make(FilesystemStorage::class, [
                'path' => $path,
                'modelClass' => ArrayObject::class,
            ]));

        return $this;
    }

    /**
     * addEloquentStorages.
     *
     * @method addEloquentStorages
     *
     * @return \Recca0120\LaravelPayum\PayumBuilder
     */
    public function addEloquentStorages()
    {
        $this->setTokenStorage($this->app->make(EloquentStorage::class, ['modelClass' => EloquentToken::class]))
            ->addStorage(EloquentPayment::class, $this->app->make(EloquentStorage::class, ['modelClass' => EloquentPayment::class]));

        return $this;
    }
}
