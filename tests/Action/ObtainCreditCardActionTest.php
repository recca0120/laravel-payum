<?php

use Mockery as m;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\ObtainCreditCard;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;

class ObtainCreditCardActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_execute()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $request = m::mock('Illuminate\Http\Request');
        $obtainCreditCardAction = new ObtainCreditCardAction($viewFactory, $request);
        $obtainCreditCard = m::mock('Payum\Core\Request\ObtainCreditCard');

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

    public function test_execute_with_post()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $request = m::mock('Illuminate\Http\Request');
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
        $this->assertInstanceOf('Payum\Core\Model\CreditCard', $obtainCreditCard->obtain());
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function test_throw_not_support()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $request = m::mock('Illuminate\Http\Request');
        $obtainCreditCardAction = new ObtainCreditCardAction($viewFactory, $request);
        $obtainCreditCard = m::mock('stdClass');

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
