<?php

namespace Delz\OAuth2Client;

use Delz\OAuth2Client\Contract\IAccessToken;
use Delz\OAuth2Client\Exception\InvalidArgumentException;

/**
 * @package Delz\OAuth2Client
 */
class AccessToken implements IAccessToken
{
    use TParameterAware;

    /**
     * 构造方法
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->setParameters($parameters);

        if(!$this->getParameter('access_token')) {
            throw new InvalidArgumentException('access_token is empty.');
        }

        $expiresAt = $this->getParameter('expires_at');
        if(!$expiresAt || !($expiresAt instanceof \DateTime)) {
            throw new InvalidArgumentException('expires_at is empty or expires_at must be instance of \Datetime.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->getParameter('access_token');
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresAt()
    {
        return $this->getParameter('expires_at');
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return $this->getParameter('expires_at') > new \DateTime();
    }

}