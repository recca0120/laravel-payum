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
        | Set
        |------------------------------------------------------------
        */

        $storage = m::mock('Payum\Core\Storage\StorageInterface');
        $registry = m::mock('Payum\Core\Registry\StorageRegistryInterface');
        $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator');
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
