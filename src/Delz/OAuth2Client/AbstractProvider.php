<?php

namespace Delz\OAuth2Client;

use Delz\OAuth2Client\Contract\IProvider;
use Delz\Common\Util\Str;
use Delz\OAuth2Client\Exception\InvalidArgumentException;
use Delz\Storage\IStorage;

/**
 * @package Mkd\OAuth2Client
 */
abstract class AbstractProvider implements IProvider
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var IStorage
     */
    protected $storage;

    /**
     * @param array $options
     * @param IStorage|null $storage 用于存储oauth state的值
     */
    public function __construct(array $options = [], IStorage $storage = null)
    {
        $this->options = $options;
        $this->storage = $storage;

        if (!$this->getClientId()) {
            throw new InvalidArgumentException('client id is empty.');
        }
        if (!$this->getClientSecret()) {
            throw new InvalidArgumentException('client secret is empty.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function redirect($redirectUrl = null)
    {
        $state = null;

        if (!is_null($redirectUrl)) {
            $this->setRedirectUrl($redirectUrl);
        }

        //如果不存在$redirectUr，抛出异常
        if (!$this->getRedirectUrl()) {
            throw new InvalidArgumentException('redirect url is empty.');
        }

        if ($this->isCsrf()) {
            if(!$this->storage) {
                throw new InvalidArgumentException('storage is not set.');
            }
            $state = Str::random(10);
            $this->storage->set('oauth_state', $state);
        }

        header("location:" . $this->getAuthorizationUrl($state));
    }

    /**
     * {@inheritdoc}
     */
    public function isCsrf()
    {
        return (bool)$this->getOption('csrf');
    }

    /**
     * {@inheritdoc}
     */
    public function checkState($state)
    {
        if(!$this->isCsrf()) {
            return true;
        }
        if(!$this->storage) {
            throw new InvalidArgumentException('storage is not set.');
        }
        return $this->storage->get('oauth_state') === $state;
    }


    /**
     * 获取授权网址
     *
     * @param string $state
     * @return string
     */
    abstract protected function getAuthorizationUrl($state);

    /**
     * 获取选项
     *
     * @param string $name
     * @return mixed
     */
    protected function getOption($name)
    {
        if (!$this->hasOption($name)) {
            return null;
        }
        return $this->options[$name];
    }

    /**
     * 是否存在某选项
     *
     * @param string $name
     * @return bool
     */
    protected function hasOption($name)
    {
        return isset($this->options[$name]) ? true : false;
    }

    /**
     * 获取跳转网址
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        return $this->getOption('redirect_url');
    }

    /**
     * 设置跳转网址
     *
     * @param string $redirectUrl
     */
    protected function setRedirectUrl($redirectUrl)
    {
        $this->options['redirect_url'] = $redirectUrl;
    }

    /**
     * client_id
     *
     * @return string
     */
    protected function getClientId()
    {
        return $this->getOption('client_id');
    }

    /**
     * client secret
     *
     * @return string
     */
    protected function getClientSecret()
    {
        return $this->getOption('client_secret');
    }

    /**
     * scope
     *
     * @return string
     */
    protected function getScope()
    {
        return $this->getOption('scope');
    }


}