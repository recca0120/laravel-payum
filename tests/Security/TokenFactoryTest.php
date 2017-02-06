<?php

namespace Recca0120\LaravelPayum\Tests\Security;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Security\TokenFactory;

class TokenFactoryTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testGenerateUrl()
    {
        $tokenFactory = m::mock(new TokenFactory(
            $storage = m::mock('Payum\Core\Storage\StorageInterface'),
            $registry = m::mock('Payum\Core\Registry\StorageRegistryInterface'),
            $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
        ))->shouldAllowMockingProtectedMethods();
        $path = 'foo';
        $parameters = ['foo' => 'bar'];
        $urlGenerator->shouldReceive('route')->once()->with($path, $parameters);
        $tokenFactory->generateUrl($path, $parameters);
    }
}
