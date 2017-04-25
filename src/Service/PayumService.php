<?php

namespace Recca0120\LaravelPayum\Service;

use Payum\Core\Request\Sync;
use Payum\Core\Request\Convert;

class PayumService
{
    /**
     * sync.
     *
     * @param string $gatewayName
     * @param callable $closure
     */
    public function sync($gatewayName, callable $closure)
    {
        $gateway = $this->getGateway($gatewayName);
        $storage = $this->getStorage();
        $payment = $storage->create();
        $closure($payment, $gatewayName, $storage, $this->getPayum());

        $request = new Sync($payment);
        $convert = new Convert($payment, 'array', $request->getToken());
        $gateway->execute($convert);
        $payment->setDetails($convert->getResult());
        $gateway->execute($request);

        return $request->getModel();
    }
}
