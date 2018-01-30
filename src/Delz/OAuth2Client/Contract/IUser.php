<?php

namespace Delz\OAuth2Client\Contract;

/**
 * Oauth返回的用户信息接口
 *
 * @package Delz\OAuth2Client\Contract
 */
interface IUser extends IParameterAware
{
    /**
     * 用户Id
     *
     * @return string
     */
    public function getId();

    /**
     * 用户昵称
     *
     * @return string
     */
    public function getNickname();

    /**
     * 用户头像地址
     *
     * @return string
     */
    public function getAvatar();

    /**
     * 用户性别
     *
     * @return string
     */
    public function getGender();
}