<?php

namespace Delz\OAuth2Client;

use Delz\OAuth2Client\Contract\IUser;
use Delz\OAuth2Client\Exception\InvalidArgumentException;

/**
 * 用户类
 *
 * @package Delz\OAuth2Client
 */
class User implements IUser
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

        if (!$this->hasParameter('id')) {
            throw new InvalidArgumentException("id is empty.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getParameter('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getNickname()
    {
        return $this->getParameter('nickname');
    }

    /**
     * {@inheritdoc}
     */
    public function getAvatar()
    {
        return $this->getParameter('avatar');
    }

    /**
     * {@inheritdoc}
     */
    public function getGender()
    {
        return $this->getParameter('gender');
    }
}