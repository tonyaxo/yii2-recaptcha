<?php

namespace recaptcha;

use yii\base\Component;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\httpclient\Client;

/**
 * @author Sergey Bogatyrev <sergey@bogatyrev.me>
 * @since 2.0
 */
class ReCaptchaComponent extends Component
{
    /**
     * Base api js url.
     */
    const API_URL = 'https://www.google.com/recaptcha/api.js';
    /**
     * Url to verify response.
     */
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    /**
     * Asset manager js api key.
     */
    const API_FILE_KEY = 'recaptcha-js-api';

    /**
     * @var string $siteKey
     */
    public $siteKey;
    /**
     * @var string $secretKey
     */
    public $secretKey;
    /**
     * @var bool whether or not send user IP address.
     */
    public $remoteIp;

    /**
     * @var Client|array|string internal HTTP client.
     */
    private $_httpClient = 'yii\httpclient\Client';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->siteKey)) {
            throw new InvalidConfigException('Property \'siteKey\' must be set.');
        }
        if (empty($this->secretKey)) {
            throw new InvalidConfigException('Property \'secretKey\' must be set.');
        }
    }

    /**
     * Sets HTTP client to be used.
     * @param array|Client $httpClient internal HTTP client.
     */
    public function setHttpClient($httpClient)
    {
        $this->_httpClient = $httpClient;
    }

    /**
     * Returns HTTP client.
     * @return Client internal HTTP client.
     */
    public function getHttpClient()
    {
        if (!is_object($this->_httpClient)) {
            $this->_httpClient = Instance::ensure($this->_httpClient, Client::className());
        }
        return $this->_httpClient;
    }

    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $response The value of 'g-recaptcha-response' in the submitted form.
     * @param string $remoteIp The end user's IP address.
     * @return bool Response from the service.
     */
    public function verify($response, $remoteIp = null)
    {
        $params = ['secret' => $this->secretKey, 'response' => $response];
        if (!is_null($remoteIp)) {
            $params['remoteip'] = $this->remoteIp;
        }

        $client = $this->getHttpClient();
        $response = $client->post(
            self::VERIFY_URL, $params, ['content-type' => 'application/x-www-form-urlencoded']
        )->send();
        $result = $response->getData();
        return $result['success'];
    }
}
