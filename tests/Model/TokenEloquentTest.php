<?php

use Mockery as m;
use Recca0120\LaravelPayum\Model\Token;

class TokenEloquentTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_set_hash()
    {
        $token = new Token();
        $token->setHash($hash = uniqid());
        $this->assertSame($hash, $token->getHash());
    }

    public function test_set_details()
    {
        $token = new Token();
        $token->setDetails($details = ['foo' => 'bar']);
        $this->assertSame($details, $token->getDetails());
    }

    public function test_set_target_url()
    {
        $token = new Token();
        $token->setTargetUrl($targetUrl = 'foo');
        $this->assertSame($targetUrl, $token->getTargetUrl());
    }

    public function test_set_after_url()
    {
        $token = new Token();
        $token->setAfterUrl($afterUrl = 'foo');
        $this->assertSame($afterUrl, $token->getAfterUrl());
    }

    public function test_set_gateway_name()
    {
        $token = new Token();
        $token->setGatewayName($gatewayName = 'foo');
        $this->assertSame($gatewayName, $token->getGatewayName());
    }
}
