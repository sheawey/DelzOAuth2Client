<?php

namespace Delz\OAuth2Client;

/**
 * 实现了IParameterAware的trait
 *
 * @package Delz\OAuth2Client
 */
trait TParameterAware
{
    /**
     * 参数数组
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * 获取所有配置参数
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * 设置配置参数
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $k => $v) {
            $this->setParameter($k, $v);
        }
    }

    /**
     * 根据key获取配置
     *
     * @param string $key
     * @return mixed
     */
    public function getParameter($key)
    {
        return $this->hasParameter($key) ? $this->parameters[strtolower($key)] : null;
    }

    /**
     * 设置单个配置
     *
     * @param string $key
     * @param mixed $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[strtolower($key)] = $value;
    }

    /**
     * 判断键值为key的参数是否存在
     *
     * @param string $key
     * @return bool
     */
    public function hasParameter($key)
    {
        return isset($this->parameters[strtolower($key)]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists(strtolower($offset), $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getParameter($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->setParameter($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->parameters[strtolower($offset)]);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($property)
    {
        return $this->getParameter($property);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getParameters();
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->getParameters(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 用魔术方法实现getXXX()方法对属性的访问
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call($name, $arguments)
    {
        if (strlen($name) <= 3) {
            throw new \RuntimeException('unknown method: ' . $name);
        }
        $getString = strtolower(substr($name, 0, 3));
        if ($getString === 'get') {
            $parameterKey = substr($name, 3, strlen($name) - 3);
            if (!$this->hasParameter($parameterKey)) {
                throw new \RuntimeException('unknown method: ' . $name);
            }
            return $this->getParameter($parameterKey);
        }
        throw new \RuntimeException('unknown method: ' . $name);
    }
}