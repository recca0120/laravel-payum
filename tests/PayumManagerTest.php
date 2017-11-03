<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\PayumManager;

class PayumManagerTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testDefaultDriver()
    {
        $manager = new PayumManager([
            'request' => $request = m::mock('Illuminate\Http\Request'),
            'payum' => m::mock('Payum\Core\Payum'),
            'config' => [
                'payum' => [
                    'default' => 'offline',
                ],
            ],
        ]);

        $this->assertInstanceOf('Recca0120\LaravelPayum\PayumDecorator', $manager->driver());
    }

    public function testOfflineDriver()
    {
        $manager = new PayumManager([
            'request' => $request = m::mock('Illuminate\Http\Request'),
            'payum' => m::mock('Payum\Core\Payum'),
            'config' => [
                'payum' => [
                    'default' => 'core',
                ],
            ],
        ]);

        $this->assertInstanceOf('Recca0120\LaravelPayum\PayumDecorator', $manager->driver('offline'));
    }
}
