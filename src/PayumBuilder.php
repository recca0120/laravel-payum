<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Model\Token as PayumToken;
use Payum\Core\PayumBuilder as CorePayumBuilder;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Recca0120\LaravelPayum\Storage\FilesystemStorage;

class PayumBuilder extends CorePayumBuilder
{
    /**
     * $app.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(ApplicationContract $app)
    {
        $this->app = $app;
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
        $this->setTokenStorage($this->app->make(FilesystemStorage::class, ['modelClass' => PayumToken::class, 'idProperty' => 'hash']))
            ->addStorage(PayumPayment::class, $this->app->make(FilesystemStorage::class, ['modelClass' => PayumPayment::class, 'idProperty' => 'number']))
            ->addStorage(ArrayObject::class, $this->app->make(FilesystemStorage::class, ['modelClass' => ArrayObject::class]));

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
