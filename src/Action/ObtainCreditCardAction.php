<?php

namespace Recca0120\LaravelPayum\Action;

use DateTime;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\CreditCard;
use Payum\Core\Request\ObtainCreditCard;
use Symfony\Component\HttpFoundation\Response;

class ObtainCreditCardAction implements ActionInterface
{
    /**
     * $viewFactory.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $viewFactory;

    /**
     * $request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     * @param \Illuminate\Http\Request           $request
     */
    public function __construct(ViewFactory $viewFactory, Request $request)
    {
        $this->viewFactory = $viewFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request ObtainCreditCard */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        if ($this->request->isMethod('POST')) {
            $creditCard = new CreditCard();
            $creditCard->setHolder($this->request->get('card_holder'));
            $creditCard->setNumber($this->request->get('card_number'));
            $creditCard->setSecurityCode($this->request->get('card_cvv'));
            $creditCard->setExpireAt(new DateTime($this->request->get('card_expire_at')));

            $request->set($creditCard);

            return;
        }

        $content = $this->viewFactory
            ->make('payum::creditcard')
            ->render();

        throw new HttpResponse(new Response($content, 200, [
            'Cache-Control' => 'no-store, no-cache, max-age=0, post-check=0, pre-check=0',
            'Pragma'        => 'no-cache',
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof ObtainCreditCard;
    }
}
