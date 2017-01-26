<?php

use Mockery as m;
use Payum\Core\Reply\ReplyInterface;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;
use Payum\Core\Model\CreditCard;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;

class ObtainCreditCardActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function test_request_isnt_instance_of_obtain_credit_card()
    {
        $obtainCreditCardAction = new ObtainCreditCardAction(
            $viewFactory = m::mock('Illuminate\Contracts\View\Factory'),
            $httpRequest = m::mock('Illuminate\Http\Request')
        );
        $obtainCreditCardAction->execute($request = m::mock('stdClass'));
    }

    public function test_http_request_method_is_post()
    {
        $obtainCreditCardAction = new ObtainCreditCardAction(
            $viewFactory = m::mock('Illuminate\Contracts\View\Factory'),
            $httpRequest = m::mock('Illuminate\Http\Request')
        );
        $cardHolder = 'foo.card_holder';
        $cardNumber = 'foo.card_number';
        $securityCode = '222';
        $expireAt = date('Y-m-d');
        $httpRequest->shouldReceive('isMethod')->andReturn(true)->once();
        $httpRequest->shouldReceive('get')->with('card_holder')->andReturn($cardHolder = 'recca')->once();
        $httpRequest->shouldReceive('get')->with('card_number')->andReturn($cardNumber = '4311952222222222')->once();
        $httpRequest->shouldReceive('get')->with('card_cvv')->andReturn($cardCvv = '222')->once();
        $httpRequest->shouldReceive('get')->with('card_expire_at')->andReturn($cardExpireAt = '2050-02-22')->once();
        $request = m::mock('Payum\Core\Request\ObtainCreditCard');
        $request->shouldReceive('set')->with(m::on(function ($creditcard) use ($cardHolder, $cardNumber, $cardCvv, $cardExpireAt) {
            $this->assertSame($cardHolder, $creditcard->getHolder());
            $this->assertSame($cardNumber, $creditcard->getNumber());
            $this->assertSame($cardCvv, $creditcard->getSecurityCode());
            $this->assertSame($cardExpireAt, $creditcard->getExpireAt()->format('Y-m-d'));

            return $creditcard instanceof CreditCard;
        }));
        $obtainCreditCardAction->execute($request);
    }

    public function test_render_template()
    {
        $obtainCreditCardAction = new ObtainCreditCardAction(
            $viewFactory = m::mock('Illuminate\Contracts\View\Factory'),
            $httpRequest = m::mock('Illuminate\Http\Request')
        );
        $httpRequest->shouldReceive('isMethod')->andReturn(false)->once();
        $request = m::mock('Payum\Core\Request\ObtainCreditCard');
        $request->shouldReceive('getToken')->andReturn($token = m::mock('stdClass'))->once();
        $request->shouldReceive('getModel')->andReturn($model = m::mock('stdClass'))->once();
        $request->shouldReceive('getFirstModel')->andReturn($firstModel = m::mock('stdClass'))->once();
        $token->shouldReceive('getTargetUrl')->andReturn($getTargetUrl = 'target_url')->once();
        $viewFactory->shouldReceive('make')->with('payum::creditcard', [
            'model' => $model,
            'firstModel' => $firstModel,
            'actionUrl' => $getTargetUrl
        ])->andReturn($form = m::mock('stdClass'))->once();
        $form->shouldReceive('render')->andReturn($html = 'form html')->once();
        try {
            $obtainCreditCardAction->execute($request);
        } catch (HttpResponse $reply) {
            $response = $reply->getResponse();
            $this->assertSame($html, $response->getContent());
        }
    }
}
