<?php

namespace Delz\OAuth2Client\Contract;

/**
 * 第三方OAuth2服务提供者
 *
 * @package Delz\OAuth2Client\Contract
 */
interface IProvider
{
    /**
     * 跳转到授权网址
     *
     * @param null|string $redirectUrl
     * @return mixed
     */
    public function redirect($redirectUrl = null);

    /**
     * 根据code获取access token
     *
     * @param string $code
     * @return IAccessToken
     */
    public function getAccessToken($code);

    /**
     * 根据AccessToken获取用户信息
     *
     * @param IAccessToken|null $token
     * @return IUser
     */
    public function getUser(IAccessToken $token = null);

    /**
     * 是否设置了需要csrf安全过滤
     *
     * @return bool
     */
    public function isCsrf();

    /**
     * 如果设置了需要csfr安全过滤，需要将跳转的网址中的state参数检查是否符合预期
     *
     * @param string $state
     * @return bool 如果state正确，返回true，否则返回false
     */
    public function checkState($state);

    /**
     * OAuth第三方服务名称
     *
     * @return string
     */
    public function getName();
}