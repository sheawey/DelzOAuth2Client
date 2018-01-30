<?php

namespace Delz\OAuth2Client;

use Delz\OAuth2Client\Contract\IProvider;

/**
 * 工厂类
 *
 * @package Delz\OAuth2Client
 */
class Factory
{
    /**
     * @var array
     */
    protected static $providers = [
        'wechat' => 'Pitaya\Component\OAuth2Client\Provider\WeChat'
    ];

    /**
     * @param string $name
     * @param array $options
     * @return IProvider
     */
    public static function create($name, array $options = [], StorageInterface $storage = null)
    {
        $name = strtolower($name);
        return new self::$providers[$name]($options, $storage);
    }
}