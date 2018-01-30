<?php

namespace Delz\OAuth2Client\Contract;

/**
 * Oauth服务工厂接口
 *
 * @package Delz\OAuth2Client\Contract
 */
interface IFactory
{
    /**
     * @return IProvider
     */
    public static function create();
}