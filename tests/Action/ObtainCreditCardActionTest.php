<?php

namespace Recca0120\LaravelPayum\Tests\Actin;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Model\CreditCard;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;

class ObtainCreditCardActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function testRequestIsntInstanceOfObtainCreditCard()
    {
        $obtainCreditCardAction = new ObtainCreditCardAction(
            $viewFactory = m::mock('Illuminate\Contracts\View\Factory'),
            $httpRequest = m::mock('Illuminate\Http\Request')
        );
        $obtainCreditCardAction->execute($request = m::mock('stdClass'));
    }

    public function testHttpRequestMethodIsPost()
    {
        $obtainCreditCardAction = new ObtainCreditCardAction(
            $viewFactory = m::mock('Illuminate\Contracts\View\Factory'),
            $httpRequest = m::mock('Illuminate\Http\Request')
        );
        $cardHolder = 'foo.card_holder';
        $cardNumber = 'foo.card_number';
        $securityCode = '222';
        $expireAt = date('Y-m-d');
        $httpRequest->shouldReceive('isMethod')->once()->andReturn(true);
        $httpRequest->shouldReceive('get')->once()->with('card_holder')->andReturn($cardHolder = 'recca');
        $httpRequest->shouldReceive('get')->once()->with('card_number')->andReturn($cardNumber = '4311952222222222');
        $httpRequest->shouldReceive('get')->once()->with('card_cvv')->andReturn($cardCvv = '222');
        $httpRequest->shouldReceive('get')->once()->with('card_expire_at')->andReturn($cardExpireAt = '2050-02-22');
        $request = m::mock('Payum\Core\Request\ObtainCreditCard');
        $request->shouldReceive('set')->once()->with(m::on(function ($creditcard) use ($cardHolder, $cardNumber, $cardCvv, $cardExpireAt) {
            $this->assertSame($cardHolder, $creditcard->getHolder());
            $this->assertSame($cardNumber, $creditcard->getNumber());
            $this->assertSame($cardCvv, $creditcard->getSecurityCode());
            $this->assertSame($cardExpireAt, $creditcard->getExpireAt()->format('Y-m-d'));

            return $creditcard instanceof CreditCard;
        }));
        $obtainCreditCardAction->execute($request);
    }

    public function testRenderTemplate()
    {
        $obtainCreditCardAction = new ObtainCreditCardAction(
            $viewFactory = m::mock('Illuminate\Contracts\View\Factory'),
            $httpRequest = m::mock('Illuminate\Http\Request')
        );
        $httpRequest->shouldReceive('isMethod')->once()->andReturn(false);
        $request = m::mock('Payum\Core\Request\ObtainCreditCard');
        $request->shouldReceive('getToken')->once()->andReturn($token = m::mock('stdClass'));
        $request->shouldReceive('getModel')->once()->andReturn($model = m::mock('stdClass'));
        $request->shouldReceive('getFirstModel')->once()->andReturn($firstModel = m::mock('stdClass'));
        $token->shouldReceive('getTargetUrl')->once()->andReturn($getTargetUrl = 'target_url');
        $viewFactory->shouldReceive('make')->once()->with('payum::creditcard', [
            'model' => $model,
            'firstModel' => $firstModel,
            'actionUrl' => $getTargetUrl,
        ])->andReturn($form = m::mock('stdClass'));
        $form->shouldReceive('render')->once()->andReturn($html = 'form html');
        try {
            $obtainCreditCardAction->execute($request);
        } catch (HttpResponse $reply) {
            $response = $reply->getResponse();
            $this->assertSame($html, $response->getContent());
        }
    }
}
