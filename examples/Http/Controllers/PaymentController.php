<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Payment;

class PaymentController extends Controller
{
    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function index()
    {
        return $this->payment->prepare('esunbank', function ($payment) {
            $payment->setNumber(uniqid());
            $payment->setCurrencyCode('TWD');
            $payment->setTotalAmount(100);
            $payment->setDescription('A description');
            $payment->setClientId('anId');
            $payment->setClientEmail('foo@example.com');
            $payment->setDetails([]);
        });
    }

    public function done(Request $request, $payumToken)
    {
        return $this->payment->done($request, $payumToken, function ($status, $payment) {
            return response()->json([
                'status'  => $status->getValue(),
                'order'   => [
                    'number'        => $payment->getNumber(),
                    'total_amount'  => $payment->getTotalAmount(),
                    'currency_code' => $payment->getCurrencyCode(),
                    'details'       => $payment->getDetails(),
                ],
            ]);
        });
    }
}
