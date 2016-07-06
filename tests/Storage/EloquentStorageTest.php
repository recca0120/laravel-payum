<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Mockery as m;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class EloquentStorageTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_storage()
    {
        $tokenClass = EloquentToken::class;
        $app = m::mock(ApplicationContract::class);
        $storage = new EloquentStorage($app, $tokenClass);
    }
}
