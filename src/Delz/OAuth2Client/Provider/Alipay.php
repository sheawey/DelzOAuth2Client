<?php

namespace Delz\OAuth2Client\Provider;

use Delz\OAuth2Client\AbstractProvider;
use Delz\OAuth2Client\Contract\IAccessToken;
use Delz\Common\Util\Url;
use Delz\OAuth2Client\Util\AlipayHelper;
use Delz\Common\Util\Http;
use Delz\OAuth2Client\AccessToken;
use Delz\OAuth2Client\User;

/**
 * 支付宝登录服务
 *
 * @package Pitaya\Component\OAuth2Client\Provider
 */
class Alipay extends AbstractProvider
{

    const API_OAUTH2_AUTHORIZE = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';
    const API_ALIPAY_GATEWAY = 'https://openapi.alipay.com/gateway.do';

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationUrl($state)
    {
        $parameters = [
            'app_id' => $this->getAppId(),
            'scope' => $this->getScope(),
            'redirect_uri' => $this->getRedirectUrl(),
            'state' => $state
        ];
        return Url::normalize(self::API_OAUTH2_AUTHORIZE, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $parameters = [
            'app_id' => $this->getAppId(),
            'method' => 'alipay.system.oauth.token',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'grant_type' => 'authorization_code',
            'code' => $code
        ];

        //去除空值
        $parameters = array_filter($parameters, 'strlen');

        $parameters['sign'] = AlipayHelper::sign($parameters, $this->getPrivateKey(), 'RSA2');

        $response = Http::post(self::API_ALIPAY_GATEWAY, ['form_params' => $parameters]);
        //支付宝默认返回是GBK编码，所以转化
        $body = iconv('GBK', 'UTF-8//IGNORE', $response->getBody());
        $data = json_decode($body, true);

        if(isset($data['error_response'])) {
            throw new \RuntimeException($data['error_response']['msg'] . ':' . $data['error_response']['sub_msg']);
        }

        if (isset($data['alipay_system_oauth_token_response']['code'])) {
            throw new \RuntimeException($data['alipay_system_oauth_token_response']['msg']);
        }

        return $this->createTokenFromResultArray($data['alipay_system_oauth_token_response']);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(IAccessToken $token = null)
    {
        $scopes = explode(',', $this->getScope());
        if(in_array('auth_user', $scopes)) {
            $parameters = [
                'app_id' => $this->getAppId(),
                'method' => 'alipay.user.info.share',
                'format' => 'JSON',
                'charset' => 'utf-8',
                'sign_type' => 'RSA2',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'auth_token' => $token->getToken()
            ];
            //去除空值
            $parameters = array_filter($parameters, 'strlen');

            $parameters['sign'] = AlipayHelper::sign($parameters, $this->getPrivateKey(), 'RSA2');

            $response = Http::post(self::API_ALIPAY_GATEWAY, ['form_params' => $parameters]);
            //支付宝默认返回是GBK编码，所以转化
            $body = iconv('GBK', 'UTF-8//IGNORE', $response->getBody());
            $data = json_decode($body, true);

            if(!AlipayHelper::verifySign($data['alipay_user_info_share_response'], $data['sign'], $this->getPublicKey(), 'RSA2')) {
                throw new \RuntimeException('Invalid sign.');
            }

            if ($data['alipay_user_info_share_response']['code'] != '10000') {
                throw new \RuntimeException($data['alipay_user_info_share_response']['msg']);
            }

            unset($data['alipay_user_info_share_response']['code']);
            unset($data['alipay_user_info_share_response']['msg']);

            return $this->createUser($data['alipay_user_info_share_response']);

        } else {
            $result['id'] = $token->getParameter('user_id');
            return new User($result);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'alipay';
    }

    /**
     * 支付宝分配给开发者的应用ID
     *
     * @return string
     */
    protected function getAppId()
    {
        return $this->hasOption('app_id') ? $this->getOption('app_id') : $this->getClientId();
    }

    /**
     * {@inheritdoc}
     */
    protected function getClientId()
    {
        return $this->hasOption('client_id') ? $this->getOption('client_id') : $this->getOption('app_id');
    }

    /**
     * 获取私钥
     *
     * @return string
     */
    protected function getPrivateKey()
    {
        return $this->getOption('private_key');
    }

    /**
     * 获取公钥
     *
     * @return string
     */
    protected function getPublicKey()
    {
        return $this->getOption('public_key');
    }

    /**
     * 将支付宝返回结果的数组转化为OAuthToken
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
     * 根据支付宝返回结果创建用户
     *
     * @param array $response
     * @return User
     */
    protected function createUser(array $response = [])
    {
        $response['id'] = $response['user_id'];

        $response['nickname'] = $response['nick_name'];
        $response['gender'] = strtolower($response['gender']);
        return new User($response);
    }

}