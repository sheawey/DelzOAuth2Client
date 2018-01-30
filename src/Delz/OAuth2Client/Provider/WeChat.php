<?php

namespace Pitaya\Component\OAuth2Client\Provider;

use Delz\OAuth2Client\AbstractProvider;
use Delz\OAuth2Client\AccessToken;
use Delz\OAuth2Client\Contract\IAccessToken;
use Delz\OAuth2Client\Exception\InvalidArgumentException;
use Delz\OAuth2Client\User;
use Delz\OAuth2Client\Contract\IUser;
use Delz\Common\Util\Url;
use Delz\Common\Util\Http;

/**
 * 微信Oauth服务
 *
 * @package Mkd\OAuth2Client\Provider
 */
class WeChat extends AbstractProvider
{
    const API_OAUTH2_AUTHORIZE = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const API_QR_CONNECT = 'https://open.weixin.qq.com/connect/qrconnect';
    const API_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const API_USER_INFO = 'https://api.weixin.qq.com/sns/userinfo';

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $parameters = [
            'appid' => $this->getClientId(),
            'secret' => $this->getClientSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $url = Url::normalize(self::API_TOKEN, $parameters);
        $result = $this->httpGet($url);
        return $this->createTokenFromResultArray($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(IAccessToken $token = null)
    {
        $openid = $token->getParameter('openid');
        if ('snsapi_base' == $this->getScope()) {
            $result['id'] = $openid;
            return new User($result);
        }
        $url = Url::normalize(self::API_USER_INFO, [
            'access_token' => $token->getToken(),
            'openid' => $openid,
            'lang' => 'zh_CN'
        ]);
        $result = $this->httpGet($url);

        return $this->createUser($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationUrl($state)
    {
        $parameters = [
            'appid' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code',
            'scope' => $this->getScope(),
            'state' => $state,
        ];
        //必须按照排序，不然无效
        ksort($parameters);

        if ($this->getScope() == 'snsapi_login') {
            return Url::normalize(self::API_QR_CONNECT, $parameters);
        } else {
            return Url::normalize(self::API_OAUTH2_AUTHORIZE, $parameters);
        }
    }

    /**
     * 将微信返回结果的数组转化为OAuthToken
     *
     * @param array $parameters
     * @return AccessToken
     */
    protected function createTokenFromResultArray(array $parameters = [])
    {
        $expiresAt = new \DateTime();
        $expiresAt->modify('+' . $parameters['expires_in'] . ' second');
        $parameters['expires_at'] = $expiresAt;
        $accessToken = new AccessToken($parameters);
        return $accessToken;
    }

    /**
     * 根据微信返回结果创建用户
     *
     * @param array $response
     * @return IUser
     */
    protected function createUser(array $response = [])
    {
        //优先unionid
        $response['id'] = isset($response['unionid']) ? $response['unionid'] : $response['openid'];
        $response['avatar'] = $response['headimgurl'];

        switch ($response['sex']) {
            case 1:
                $response['gender'] = 'm';
                break;
            case 2:
                $response['gender'] = 'f';
                break;
            default:
                $response['gender'] = 'u';
                break;
        }
        return new User($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wechat';
    }

    /**
     * 对微信的请求封装
     *
     * @param string $url
     * @param array $params
     * @return mixed
     */
    protected function httpGet($url, array $params = [])
    {
        $response = Http::get($url, $params);
        $result = json_decode($response->getBody(), true);
        if (isset($result['errcode'])) {
            throw new InvalidArgumentException($result['errcode'], $result['errmsg']);
        }
        return $result;
    }
}