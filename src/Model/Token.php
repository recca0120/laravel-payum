<?php

namespace Recca0120\LaravelPayum\Model;

use Payum\Core\Security\Util\Random;
use Illuminate\Database\Eloquent\Model;
use Payum\Core\Security\TokenInterface;

class Token extends Model implements TokenInterface
{
    /**
     * $incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    /**
     * $table.
     *
     * @var string
     */
    protected $table = 'payum_tokens';

    /**
     * $primaryKey.
     *
     * @var string
     */
    protected $primaryKey = 'hash';

    /**
     * $unguarded.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * __construct.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes['hash'] = empty($attributes['hash']) === true ? Random::generateToken() : $attributes['hash'];
        parent::__construct($attributes);
    }

    /**
     * getHash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->getAttribute('hash');
    }

    /**
     * setHash.
     *
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->setAttribute('hash', $hash);
    }

    /**
     * setDetails.
     *
     * @param mixed $details
     */
    public function setDetails($details)
    {
        $this->setAttribute('details', serialize($details));
    }

    /**
     * getDetails.
     *
     * @return mixed
     */
    public function getDetails()
    {
        return unserialize($this->getAttribute('details'));
    }

    /**
     * getTargetUrl.
     *
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->getAttribute('target_url');
    }

    /**
     * setTargetUrl.
     *
     * @param string $targetUrl
     */
    public function setTargetUrl($targetUrl)
    {
        $this->setAttribute('target_url', $targetUrl);
    }

    /**
     * getAfterUrl.
     *
     * @return string
     */
    public function getAfterUrl()
    {
        return $this->getAttribute('after_url');
    }

    /**
     * setAfterUrl.
     *
     * @param string $afterUrl
     */
    public function setAfterUrl($afterUrl)
    {
        $this->setAttribute('after_url', $afterUrl);
    }

    /**
     * getGatewayName.
     *
     * @return string
     */
    public function getGatewayName()
    {
        return $this->getAttribute('gateway_name');
    }

    /**
     * setGatewayName.
     *
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->setAttribute('gateway_name', $gatewayName);
    }
}
