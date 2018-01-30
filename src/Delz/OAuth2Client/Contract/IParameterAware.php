<?php

namespace Delz\OAuth2Client\Contract;

/**
 * 具有很多参数的模型对象接口
 *
 * 比如一个用户对象 User，他可能有如下属性：
 *  age
 *  gender
 *  birth
 *  ...
 *
 * 但是这些属性对于用户对象来说是不确定的，可能有的对象有，有的没有。
 *
 * 这种情况的User对象可以实现本接口
 *
 * @package Delz\OAuth2Client\Contract
 */
interface IParameterAware extends \ArrayAccess
{
    /**
     * 获取所有属性
     *
     * @return array
     */
    public function getParameters();

    /**
     * 设置所有属性
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters);

    /**
     * 获取指定的属性
     *
     * @param string $key
     * @return mixed
     */
    public function getParameter($key);

    /**
     * 设置指定的属性
     *
     * @param string $key
     * @param mixed $value
     */
    public function setParameter($key, $value);

    /**
     * 判断是否有指定的属性
     *
     * @param string $key
     * @return bool
     */
    public function hasParameter($key);

    /**
     * 将所有属性转化为数组输出
     *
     * @return array
     */
    public function toArray();

    /**
     * 将所有属性转化为json格式输出
     *
     * @return string
     */
    public function toJSON();
}