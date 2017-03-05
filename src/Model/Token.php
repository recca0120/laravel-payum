<?php

namespace Recca0120\LaravelPayum\Model;

use Payum\Core\Security\Util\Random;
use Illuminate\Database\Eloquent\Model;
use Payum\Core\Security\TokenInterface;

class Token extends Model implements TokenInterface
{
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
     * $incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
        return $this->getAttribute('targetUrl');
    }

    /**
     * setTargetUrl.
     *
     * @param string $targetUrl
     */
    public function setTargetUrl($targetUrl)
    {
        $this->setAttribute('targetUrl', $targetUrl);
    }

    /**
     * getAfterUrl.
     *
     * @return string
     */
    public function getAfterUrl()
    {
        return $this->getAttribute('afterUrl');
    }

    /**
     * setAfterUrl.
     *
     * @param string $afterUrl
     */
    public function setAfterUrl($afterUrl)
    {
        $this->setAttribute('afterUrl', $afterUrl);
    }

    /**
     * getGatewayName.
     *
     * @return string
     */
    public function getGatewayName()
    {
        return $this->getAttribute('gatewayName');
    }

    /**
     * setGatewayName.
     *
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->setAttribute('gatewayName', $gatewayName);
    }
}
