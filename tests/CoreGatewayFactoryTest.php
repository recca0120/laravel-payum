<?php

use Mockery as m;
use Payum\Core\Gateway;
use Payum\Core\Bridge\Spl\ArrayObject;
use Recca0120\LaravelPayum\CoreGatewayFactory;

class CoreGatewayFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_build_action()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $actionInterface = m::mock('Payum\Core\Action\ActionInterface');
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $defaultConfig = new ArrayObject();
        $coreGatewayFactory = m::mock(new CoreGatewayFactory($app, []));
        $gateway = new Gateway();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $coreGatewayFactory
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $app
            ->shouldReceive('offsetGet')->with('payum.action.foo1')->once()->andReturn($actionInterface);

        $defaultConfig->defaults([
            'payum.prepend_actions' => [],
            'payum.action.foo1' => 'payum.action.foo1',
        ]);
        $coreGatewayFactory->buildActions($gateway, $defaultConfig);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertAttributeSame([$actionInterface], 'actions', $gateway);
    }

    public function test_build_api()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $api = m::mock('stdClass');
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $defaultConfig = new ArrayObject();
        $coreGatewayFactory = m::mock(new CoreGatewayFactory($app, []));
        $gateway = new Gateway();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $coreGatewayFactory
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $app
            ->shouldReceive('offsetGet')->with('payum.api.foo1')->once()->andReturn($api);

        $defaultConfig->defaults([
            'payum.prepend_apis' => [],
            'payum.api.foo1' => 'payum.api.foo1',
        ]);
        $coreGatewayFactory->buildApis($gateway, $defaultConfig);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertAttributeSame([$api], 'apis', $gateway);
    }

    public function test_build_extensions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $extensionInterface = m::mock('Payum\Core\Extension\ExtensionInterface');
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $defaultConfig = new ArrayObject();
        $coreGatewayFactory = m::mock(new CoreGatewayFactory($app, []));
        $gateway = new Gateway();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $coreGatewayFactory
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $app->shouldReceive('offsetGet')->with('payum.extension.foo1')->once()->andReturn($extensionInterface);

        $defaultConfig->defaults([
            'payum.prepend_extensions' => [],
            'payum.extension.foo1' => 'payum.extension.foo1',
        ]);
        $coreGatewayFactory->buildExtensions($gateway, $defaultConfig);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeSame([$extensionInterface], 'extensions', $extensions);
    }
}
