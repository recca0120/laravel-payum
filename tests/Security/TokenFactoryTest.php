<?php

use Mockery as m;
use Recca0120\LaravelPayum\Security\TokenFactory;

class TokenFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_generate_url()
    {
        $tokenFactory = m::mock(new TokenFactory(
            $storage = m::mock('Payum\Core\Storage\StorageInterface'),
            $registry = m::mock('Payum\Core\Registry\StorageRegistryInterface'),
            $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
        ))->shouldAllowMockingProtectedMethods();
        $path = 'foo';
        $parameters = ['foo' => 'bar'];
        $urlGenerator->shouldReceive('route')->with($path, $parameters)->once();
        $tokenFactory->generateUrl($path, $parameters);
    }
}
