<?php

use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Mockery as m;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Security\TokenFactory;

class TokenFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_generate_url()
    {
        $gatewayName = 'gateway';
        $model = $model = '';
        $trargetPath = 'target';
        $targetParameters = ['target'];
        $hash = 'hash';

        $token = m::mock(stdClass::class)
            ->shouldReceive('getHash')->twice()->andReturn($hash)
            ->shouldReceive('setHash')->once()->andReturnSelf()
            ->shouldReceive('setGatewayName')->once()->andReturnSelf()
            ->shouldReceive('setDetails')->andReturnSelf()
            ->shouldReceive('setTargetUrl')
            ->mock();

        $storage = m::mock(StorageInterface::class)
            ->shouldReceive('create')->once()->andReturn($token)
            ->shouldReceive('update')->andReturnSelf()
            ->mock();

        $registry = m::mock(StorageRegistryInterface::class)
            ->shouldReceive('getStorage')->andReturnSelf()
            ->shouldReceive('identify')->andReturnSelf()
            ->mock();

        $urlGenerator = m::mock(UrlGeneratorContract::class)
            ->shouldReceive('route')->with($trargetPath, array_replace([
                'payum_token' => $hash,
            ], $targetParameters))->once()
            ->mock();

        $tokenFactory = new TokenFactory($storage, $registry, $urlGenerator);
        $tokenFactory->createToken($gatewayName, $model, $trargetPath, $targetParameters);
    }
}
