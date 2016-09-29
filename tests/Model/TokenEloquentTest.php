<?php

use Mockery as m;
use Recca0120\LaravelPayum\Model\Token;

class TokenEloquentTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_set_attributes()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $token = m::mock(new Token());
        $creditcard = m::mock('Payum\Core\Model\CreditCardInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedHash = 'fooHash';
        $exceptedDetails = [
            'foo',
            'bar',
        ];
        $exceptedTargetUrl = 'fooTargetUrl';
        $exceptedAfterUrl = 'fooAfterUrl';
        $exceptedgetGatewayName = 'fooGatewayName';

        $token->setHash($exceptedHash);
        $token->setDetails($exceptedDetails);
        $token->setTargetUrl($exceptedTargetUrl);
        $token->setAfterUrl($exceptedAfterUrl);
        $token->setGatewayName($exceptedgetGatewayName);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedHash, $token->getHash());
        $this->assertSame($exceptedDetails, $token->getDetails());
        $this->assertSame($exceptedTargetUrl, $token->getTargetUrl());
        $this->assertSame($exceptedAfterUrl, $token->getAfterUrl());
        $this->assertSame($exceptedgetGatewayName, $token->getGatewayName());
    }
}
