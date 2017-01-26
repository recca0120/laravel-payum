<?php

namespace Recca0120\LaravelPayum\Action;

use DateTime;
use Illuminate\Http\Request;
use Payum\Core\Model\CreditCard;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\ObtainCreditCard;
use Symfony\Component\HttpFoundation\Response;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Payum\Core\Exception\RequestNotSupportedException;

class ObtainCreditCardAction implements ActionInterface
{
    /**
     * $viewFactory.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $viewFactory;

    /**
     * $httpRequest.
     *
     * @var \Illuminate\Http\Request
     */
    protected $httpRequest;

    /**
     * $templateName.
     *
     * @var string
     */
    protected $templateName = 'payum::creditcard';

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     * @param \Illuminate\Http\Request           $httpRrequest
     * @param string                             $templateName
     */
    public function __construct(ViewFactory $viewFactory, Request $request)
    {
        $this->viewFactory = $viewFactory;
        $this->httpRequest = $request;
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

        if ($this->httpRequest->isMethod('POST')) {
            $creditCard = new CreditCard();
            $creditCard->setHolder($this->httpRequest->get('card_holder'));
            $creditCard->setNumber($this->httpRequest->get('card_number'));
            $creditCard->setSecurityCode($this->httpRequest->get('card_cvv'));
            $creditCard->setExpireAt(new DateTime($this->httpRequest->get('card_expire_at')));

            $request->set($creditCard);

            return;
        }

        $form = $this->viewFactory->make($this->templateName, [
            'model' => $request->getModel(),
            'firstModel' => $request->getFirstModel(),
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        ]);

        throw new HttpResponse(new Response($form->render(), 200, [
            'Cache-Control' => 'no-store, no-cache, max-age=0, post-check=0, pre-check=0',
            'X-Status-Code' => 200,
            'Pragma' => 'no-cache',
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
