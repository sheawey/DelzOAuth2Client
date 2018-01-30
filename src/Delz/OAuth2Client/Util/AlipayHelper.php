<?php

namespace Delz\OAuth2Client\Util;

/**
 * 支付宝助手类
 *
 * @package Delz\OAuth2Client\Util
 */
class AlipayHelper
{
    const KEY_TYPE_PUBLIC = 1; //公钥
    const KEY_TYPE_PRIVATE = 2; //私钥

    /**
     * 验签的内容格式类型
     */
    const TYPE_VERIFY_CONTENT_JSON = 1; //JSON
    const TYPE_VERIFY_CONTENT_QUERY = 2; //QUERY url拼接型

    /**
     * 将数组形式的支付业务参数转化成json格式的字符串
     *
     * @param array $bizContent 支付业务参数
     * @return string
     */
    public static function convertBizContentToString(array $bizContent)
    {
        //去掉数组中空值
        $bizContent = array_filter($bizContent, "strlen");
        return json_encode($bizContent);
    }


    /**
     * 生成签名
     *
     * @param array $parameters 要签名的参数集合
     * @param string $key 私钥或私钥文件路径
     * @param string $type 签名类型，支持RSA和RSA2
     *
     * @return string
     * @throws \InvalidArgumentException 如果签名类型不是RSA或RSA2，抛出异常
     */
    public static function sign(array $parameters, $key, $type = 'RSA2')
    {
        //去除加密参数中的空值
        $parameters = array_filter($parameters, 'strlen');

        //去除sign键值，如果存在
        unset($parameters['sign']);

        ksort($parameters);

        //待签名的字符串
        $signStr = urldecode(http_build_query($parameters));

        switch ($type) {
            case 'RSA2':
                $alg = OPENSSL_ALGO_SHA256;
                break;
            case 'RSA':
                $alg = OPENSSL_ALGO_SHA1;
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Sign type of "%s" is not support.', $type)
                );
                break;
        }

        $key = self::format($key, self::KEY_TYPE_PRIVATE);

        $privateId = openssl_pkey_get_private($key);

        $sign = '';

        openssl_sign($signStr, $sign, $privateId, $alg);
        openssl_free_key($privateId);

        return base64_encode($sign);
    }


    /**
     * 验签
     *
     * @param array $parameters 要验签的参数
     * @param string $sign 签名
     * @param string $key 公钥
     * @param string $type 类型
     * @param int $contentType 内容类型
     * @return bool
     */
    public static function verifySign(array $parameters, $sign, $key, $type = 'RSA2', $contentType = self::TYPE_VERIFY_CONTENT_JSON)
    {
        switch ($type) {
            case 'RSA2':
                $alg = OPENSSL_ALGO_SHA256;
                break;
            case 'RSA':
                $alg = OPENSSL_ALGO_SHA1;
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Sign type of "%s" is not support.', $type)
                );
                break;
        }

        unset($parameters['sign']);
        unset($parameters['sign_type']);

        //待签名的字符串
        if($contentType == self::TYPE_VERIFY_CONTENT_JSON) {
            $signStr = json_encode($parameters, JSON_UNESCAPED_UNICODE);
        } else {
            ksort($parameters);
            $signStr = urldecode(http_build_query($parameters));
        }

        //阿里云接口的编码是GBK
        $signStr = iconv('UTF-8','GBK//IGNORE', $signStr);
        $key = self::format($key, self::KEY_TYPE_PUBLIC);
        $privateId = openssl_pkey_get_public($key);
        $result = (bool)openssl_verify($signStr, base64_decode($sign), $privateId, $alg);
        openssl_free_key($privateId);

        return $result;

    }


    /**
     * 格式化私钥或者公钥
     *
     * @param string $key 密钥或密钥文件路径
     * @param int $type 密钥类型：公钥、私钥
     * @return string
     */
    private static function format($key, $type = self::KEY_TYPE_PUBLIC)
    {
        if (is_file($key)) {
            return file_get_contents($key);
        }

        if (is_string($key) && strpos($key, '-----') === false) {
            $keyLines = [];
            if ($type == self::KEY_TYPE_PUBLIC) {
                $keyLines[] = '-----BEGIN PUBLIC KEY-----';
            } else {
                $keyLines[] = '-----BEGIN RSA PRIVATE KEY-----';
            }

            $i = 0;
            while ($keyStr = substr($key, $i * 64, 64)) {
                $keyLines[] = $keyStr;
                $i++;
            }
            if ($type == self::KEY_TYPE_PUBLIC) {
                $keyLines[] = '-----END PUBLIC KEY-----';
            } else {
                $keyLines[] = '-----END RSA PRIVATE KEY-----';
            }


            return implode("\n", $keyLines);

        }

        return $key;
    }

}