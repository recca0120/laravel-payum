<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery as m;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class TokenTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_update()
    {
        $hash = uniqid();
        $details = [uniqid()];
        $targetUrl = uniqid();
        $afterUrl = uniqid();
        $gatewayName = uniqid();

        $tokenClass = EloquentToken::class;
        $token = m::mock(new $tokenClass())
            ->shouldReceive('save')->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($tokenClass)->once()->andReturn($token)
            ->mock();
        $storage = new EloquentStorage($app, $tokenClass);
        $token = $storage->create();

        $token->setHash($hash);
        $token->setDetails($details);
        $token->setTargetUrl($targetUrl);
        $token->setAfterUrl($afterUrl);
        $token->setGatewayName($gatewayName);

        $this->assertSame($token->getHash(), $hash);
        $this->assertSame($token->getDetails(), $details);
        $this->assertSame($token->getTargetUrl(), $targetUrl);
        $this->assertSame($token->getAfterUrl(), $afterUrl);
        $this->assertSame($token->getGatewayName(), $gatewayName);

        $storage->update($token);
    }

    public function test_delete()
    {
        $tokenClass = EloquentToken::class;

        $token = m::mock(new $tokenClass())
            ->shouldReceive('delete')->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($tokenClass)->once()->andReturn($token)
            ->mock();

        $storage = new EloquentStorage($app, $tokenClass);
        $token = $storage->create();

        $storage->delete($token);
    }

    public function test_find()
    {
        $tokenClass = EloquentToken::class;

        $token = m::mock(new $tokenClass())
            ->shouldReceive('find')->with(1)->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($tokenClass)->once()->andReturn($token)
            ->mock();

        $storage = new EloquentStorage($app, $tokenClass);
        $token = $storage->find(1);
    }

    public function test_identify()
    {
        $hash = uniqid();

        $tokenClass = EloquentToken::class;
        $token = m::mock(new $tokenClass())
            ->shouldReceive('getKey')->andReturn($hash)
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($tokenClass)->once()->andReturn($token)
            ->mock();
        $storage = new EloquentStorage($app, $tokenClass);
        $token = $storage->create();

        $this->assertSame($storage->identify($token)->getId(), $hash);
    }

    public function test_find_by()
    {
        $hash = uniqid();
        $targetUrl = uniqid();

        $builder = m::mock(Builder::class)
            ->shouldReceive('where')->with('hash', '=', $hash)->once()->andReturnSelf()
            ->shouldReceive('where')->with('targetUrl', '=', $targetUrl)->once()->andReturnSelf()
            ->shouldReceive('get')->andReturn(new Collection())
            ->mock();

        $tokenClass = EloquentToken::class;
        $token = m::mock(new $tokenClass())
            ->shouldReceive('newQuery')->once()->andReturn($builder)
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($tokenClass)->once()->andReturn($token)
            ->mock();

        $storage = new EloquentStorage($app, $tokenClass);
        $storage->findBy([
            'hash'      => $hash,
            'targetUrl' => $targetUrl,
        ]);
    }
}
