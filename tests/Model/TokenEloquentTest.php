<?php

namespace Recca0120\LaravelPayum\Tests\Model;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Model\Token;

class TokenEloquentTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testSetHash()
    {
        $token = new Token();
        $token->setHash($hash = uniqid());
        $this->assertSame($hash, $token->getHash());
    }

    public function testSetDetails()
    {
        $token = new Token();
        $token->setDetails($details = ['foo' => 'bar']);
        $this->assertSame($details, $token->getDetails());
    }

    public function testSetTargetUrl()
    {
        $token = new Token();
        $token->setTargetUrl($targetUrl = 'foo');
        $this->assertSame($targetUrl, $token->getTargetUrl());
    }

    public function testSetAfterUrl()
    {
        $token = new Token();
        $token->setAfterUrl($afterUrl = 'foo');
        $this->assertSame($afterUrl, $token->getAfterUrl());
    }

    public function testSetGatewayName()
    {
        $token = new Token();
        $token->setGatewayName($gatewayName = 'foo');
        $this->assertSame($gatewayName, $token->getGatewayName());
    }
}
