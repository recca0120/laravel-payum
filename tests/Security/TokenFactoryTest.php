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
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $storage = m::mock(StorageInterface::class);
        $registry = m::mock(StorageRegistryInterface::class);
        $urlGenerator = m::mock(UrlGeneratorContract::class);
        $tokenFactory = m::mock(new TokenFactory($storage, $registry, $urlGenerator))
            ->shouldAllowMockingProtectedMethods();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedPath = 'foo.bar';
        $exceptedParameters = [
            'buzz',
            'fuzz',
        ];
        $exceptedUrl = 'fooUrl';

        $urlGenerator->shouldReceive('route')->with($exceptedPath, $exceptedParameters)->andReturn($exceptedUrl);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedUrl, $tokenFactory->generateUrl($exceptedPath, $exceptedParameters));
    }
}
