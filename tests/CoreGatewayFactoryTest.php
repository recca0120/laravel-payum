<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Mockery as m;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Gateway;
use Recca0120\LaravelPayum\CoreGatewayFactory;

class CoreGatewayFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testBuildAction()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $actionInterface = m::mock(ActionInterface::class);
        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class);
        $defaultConfig = new ArrayObject();
        $coreGateway = m::mock(new CoreGatewayFactory($app, []));
        $gateway = new Gateway();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $coreGateway->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $app->shouldReceive('offsetGet')->with('payum.action.foo1')->once()->andReturn($actionInterface);

        $defaultConfig->defaults([
            'payum.prepend_actions' => [],
            'payum.action.foo1'     => 'payum.action.foo1',
        ]);
        $coreGateway->buildActions($gateway, $defaultConfig);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertAttributeSame([$actionInterface], 'actions', $gateway);
    }

    public function testBuildApi()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $api = m::mock(stdClass::class);
        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class);
        $defaultConfig = new ArrayObject();
        $coreGateway = m::mock(new CoreGatewayFactory($app, []));
        $gateway = new Gateway();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $coreGateway->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $app->shouldReceive('offsetGet')->with('payum.api.foo1')->once()->andReturn($api);

        $defaultConfig->defaults([
            'payum.prepend_apis' => [],
            'payum.api.foo1'     => 'payum.api.foo1',
        ]);
        $coreGateway->buildApis($gateway, $defaultConfig);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertAttributeSame([$api], 'apis', $gateway);
    }

    public function testbuildExtensions()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $extensionInterface = m::mock(ExtensionInterface::class);
        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class);
        $defaultConfig = new ArrayObject();
        $coreGateway = m::mock(new CoreGatewayFactory($app, []));
        $gateway = new Gateway();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $coreGateway->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $app->shouldReceive('offsetGet')->with('payum.extension.foo1')->once()->andReturn($extensionInterface);

        $defaultConfig->defaults([
            'payum.prepend_extensions' => [],
            'payum.extension.foo1'     => 'payum.extension.foo1',
        ]);
        $coreGateway->buildExtensions($gateway, $defaultConfig);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeSame([$extensionInterface], 'extensions', $extensions);
    }
}
