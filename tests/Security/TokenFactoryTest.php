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
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $storage = m::spy('Payum\Core\Storage\StorageInterface');
        $registry = m::spy('Payum\Core\Registry\StorageRegistryInterface');
        $urlGenerator = m::spy('Illuminate\Contracts\Routing\UrlGenerator');
        $path = 'foo';
        $parameters = [];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $tokenFactory = m::mock(new TokenFactory($storage, $registry, $urlGenerator))->shouldAllowMockingProtectedMethods();
        $tokenFactory->generateUrl($path, $parameters);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $urlGenerator->shouldHaveReceived('route')->with($path, $parameters);
    }
}
