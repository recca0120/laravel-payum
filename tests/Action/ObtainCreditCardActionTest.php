<?php

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Mockery as m;
use Payum\Core\Model\CreditCard;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\ObtainCreditCard;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;

class ObtainCreditCardActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $viewFactory = m::mock(Factory::class);
        $request = m::mock(Request::class);
        $obtainCreditCardAction = new ObtainCreditCardAction($viewFactory, $request);
        $obtainCreditCard = m::mock(ObtainCreditCard::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'foo';
        $request->shouldReceive('isMethod')->andReturn(false);

        $viewFactory->shouldReceive('make')->andReturnSelf()
            ->shouldReceive('render')->andReturn($excepted);

        $obtainCreditCard
            ->shouldReceive('getModel')
            ->shouldReceive('getFirstModel')
            ->shouldReceive('getToken');

        try {
            $obtainCreditCardAction->execute($obtainCreditCard);
        } catch (ReplyInterface $response) {
        }

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame($excepted, $response->getResponse()->getContent());
    }

    public function testExecuteWithPost()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $viewFactory = m::mock(Factory::class);
        $request = m::mock(Request::class);
        $obtainCreditCardAction = new ObtainCreditCardAction($viewFactory, $request);
        $obtainCreditCard = new ObtainCreditCard();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('isMethod')->andReturn(true)
            ->shouldReceive('get')->with('card_holder')->andReturn('card_holder')
            ->shouldReceive('get')->with('card_number')->andReturn('card_number')
            ->shouldReceive('get')->with('card_cvv')->andReturn('111')
            ->shouldReceive('get')->with('card_expire_at')->andReturn(date('Y-m-d'));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertNull($obtainCreditCardAction->execute($obtainCreditCard));
        $this->assertInstanceOf(CreditCard::class, $obtainCreditCard->obtain());
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function testThrowNotSupport()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $viewFactory = m::mock(Factory::class);
        $request = m::mock(Request::class);
        $obtainCreditCardAction = new ObtainCreditCardAction($viewFactory, $request);
        $obtainCreditCard = m::mock(stdClass::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $obtainCreditCardAction->execute($obtainCreditCard);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
    }
}
