<?php

namespace Recca0120\LaravelPayum\Security;

use Payum\Core\Storage\StorageInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Payum\Core\Registry\StorageRegistryInterface;

class TokenFactory extends AbstractTokenFactory
{
    /**
     * $urlGenerator.
     *
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Payum\Core\Storage\StorageInterface          $tokenStorage
     * @param \Payum\Core\Registry\StorageRegistryInterface $storageRegistry
     * @param \Illuminate\Contracts\Routing\UrlGenerator    $urlGenerator
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, UrlGenerator $urlGenerator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * generateUrl.
     *
     * @method generateUrl
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function generateUrl($path, array $parameters = [])
    {
        return $this->urlGenerator->route($path, $parameters);
    }
}
