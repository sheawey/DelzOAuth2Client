<?php

namespace Delz\OAuth2Client;

use Delz\OAuth2Client\Contract\IProvider;
use Delz\Storage\IStorage;

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
        'wechat' => 'Pitaya\Component\OAuth2Client\Provider\WeChat',
        'alipay' => 'Pitaya\Component\OAuth2Client\Provider\Alipay'
    ];

    /**
     * @param string $name
     * @param array $options
     * @param IStorage|null $storage
     * @return IProvider
     */
    public static function create($name, array $options = [], IStorage $storage = null)
    {
        $name = strtolower($name);
        return new self::$providers[$name]($options, $storage);
    }
}