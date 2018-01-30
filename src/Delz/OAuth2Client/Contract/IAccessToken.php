<?php

namespace Delz\OAuth2Client\Contract;

/**
 * @package Delz\OAuth2Client\Contract
 */
interface IAccessToken extends IParameterAware
{
    /**
     * 获取token值
     *
     * @return string
     */
    public function getToken();

    /**
     * 获取token过期时间
     *
     * @return \DateTime
     */
    public function getExpiresAt();

    /**
     * 判断token是否有效
     *
     * @return bool
     */
    public function isAvailable();
}