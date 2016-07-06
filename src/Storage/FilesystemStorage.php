<?php

namespace Recca0120\LaravelPayum\Storage;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;
use Payum\Core\Storage\FilesystemStorage as PayumFilesystemStorage;

class FilesystemStorage extends PayumFilesystemStorage
{
    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Filesystem\Filesystem            $filesystem
     * @param mixed                                        $modelClass
     * @param string                                       $idProperty
     */
    public function __construct(ApplicationContract $app, Filesystem $filesystem, $modelClass, $idProperty = 'payum_id')
    {
        $storagePath = $app->storagePath().'/payum/';
        if ($filesystem->isDirectory($storagePath) === false) {
            $filesystem->makeDirectory($storagePath, 0777, true);
        }
        parent::__construct($storagePath, $modelClass, $idProperty);
    }
}
