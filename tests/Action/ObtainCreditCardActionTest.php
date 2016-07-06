<?php

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Mockery as m;
use Payum\Core\Model\CreditCard;
use Payum\Core\Request\ObtainCreditCard;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;

class ObtainCreditCardActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_execute_with_card_data()
    {
        $viewFactory = m::mock(ViewFactory::class);

        $request = m::mock(Request::class)
            ->shouldReceive('isMethod')->with('POST')->andReturn(true)
            ->shouldReceive('get')->with('card_holder')->andReturn('name')
            ->shouldReceive('get')->with('card_number')->andReturn('12345')
            ->shouldReceive('get')->with('card_cvv')->andReturn('111')
            ->shouldReceive('get')->with('card_expire_at')->andReturn(date('Y-m'))
            ->mock();

        $obtainCreditCard = m::mock(ObtainCreditCard::class)
            ->shouldReceive('set')->with(m::type(CreditCard::class))
            ->mock();

        $httpRequestAction = new ObtainCreditCardAction($viewFactory, $request);
        $httpRequestAction->execute($obtainCreditCard);
    }

    /**
     * @expectedException \Payum\Core\Bridge\Symfony\Reply\HttpResponse
     */
    public function test_execute_without_card_data()
    {
        $view = m::mock(View::class)
            ->shouldReceive('render')->andReturn('')
            ->mock();

        $viewFactory = m::mock(ViewFactory::class)
            ->shouldReceive('make')->with('payum::creditcard')->andReturn($view)
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('isMethod')->with('POST')->andReturn(false)
            ->mock();

        $obtainCreditCard = m::mock(ObtainCreditCard::class);

        $httpRequestAction = new ObtainCreditCardAction($viewFactory, $request);
        $httpRequestAction->execute($obtainCreditCard);
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function test_execute_except_not_support()
    {
        $viewFactory = m::mock(ViewFactory::class);
        $request = m::mock(Request::class);
        $httpRequestAction = new ObtainCreditCardAction($viewFactory, $request);
        $httpRequestAction->execute([]);
    }
}
