<?php

namespace Quantum\Libraries\Captcha\Adapters;

use Quantum\Libraries\Captcha\CaptchaInterface;
use Quantum\Libraries\Curl\HttpClient;

class HcaptchaAdapter implements CaptchaInterface
{
    const CLIENT_API = 'https://hcaptcha.com/1/api.js';
    const VERIFY_URL = 'https://hcaptcha.com/siteverify';

    /**
     * The hCaptcha secret key.
     *
     * @var string
     */
    protected $secretkey;

    /**
     * The hCaptcha sitekey key.
     *
     * @var string
     */
    protected $sitekey;

    protected $type;

    /**
     * The cached verified responses.
     *
     * @var array
     */
    protected $verifiedResponses = [];

    private static $instance = null;

    protected $http;

    /**
     * HcaptchaAdapter.
     *
     * @param array $params
     */
    private function __construct(array $params)
    {
        $this->http = new HttpClient();
        
        $this->secretkey = $params['secret_key'];
        $this->sitekey = $params['site_key'];
        $this->type = $params['type'];
    }

    /**
     * Get Instance
     * @param array $params
     * @return HcaptchaAdapter
     */
    public static function getInstance(array $params): HcaptchaAdapter
    {
        if (self::$instance === null) {
            self::$instance = new self($params);
        }

        return self::$instance;
    }

    /**
     * Render HTML captcha.
     *
     * @param array $attributes
     *
     * @return string
     */
    public function display(string $formIdentifier = '', array $attributes = []): string
    {
        $captchaElement = '';
        if (strtolower($this->type) == 'visible'){
            $captchaElement = $this->getVisibleElement($attributes);
        } elseif (strtolower($this->type) == 'invisible') {
            $captchaElement = $this->getInvisibleElement($formIdentifier);
        }
        return $captchaElement;
    }

    /**
     * Render js source
     *
     * @param null   $lang
     * @param bool   $callback
     * @param string $onLoadClass
     *
     * @return string
     */
    public function renderJs($lang = null, $callback = false, $onLoadClass = 'onloadCallBack'): string
    {
        return '<script src="' . $this->getJsLink($lang, $callback, $onLoadClass) . '" async defer></script>' . "\n";
    }

    /**
     * Verify hCaptcha response.
     *
     * @param string $response
     * @param string $clientIp
     *
     * @return bool
     */
    public function verifyResponse(string $response, string $clientIp = null): bool
    {
        if (empty($response)) {
            return false;
        }

        // Return true if response already verfied before.
        if (in_array($response, $this->verifiedResponses)) {
            return true;
        }

        $verifyResponse = $this->sendRequestVerify([
            'secret'   => $this->secretkey,
            'response' => $response,
        ]);

        if (isset($verifyResponse['success']) && $verifyResponse['success'] === true) {
            // A response can only be verified once from hCaptcha, so we need to
            // cache it to make it work in case we want to verify it multiple times.
            $this->verifiedResponses[] = $response;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get hCaptcha js link.
     *
     * @param string  $lang
     * @param boolean $callback
     * @param string  $onLoadClass
     *
     * @return string
     */
    public function getJsLink(string $lang = null, bool $callback = false, string $onLoadClass = 'onloadCallBack'): string
    {
        $client_api = static::CLIENT_API;
        $params = [];

        $callback ? $this->setCallBackParams($params, $onLoadClass) : false;
        $lang ? $params['hl'] = $lang : null;

        return $client_api . '?' . http_build_query($params);
    }

    /**
     * @param $params
     * @param $onLoadClass
     */
    protected function setCallBackParams(&$params, $onLoadClass)
    {
        $params['render'] = 'explicit';
        $params['onload'] = $onLoadClass;
    }

    /**
     * Send verify request.
     *
     * @param array $query
     *
     * @return array
     */
    protected function sendRequestVerify(array $query = []): array
    {
        $this->http->createRequest(static::VERIFY_URL)->setMethod('POST')->setData($query)->start();

        return (array)$this->http->getResponseBody();
    }

    /**
     * Prepare HTML attributes and assure that the correct classes and attributes for captcha are inserted.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function prepareAttributes(array $attributes): array
    {
        $attributes['data-sitekey'] = $this->sitekey;
        if (!isset($attributes['class'])) {
            $attributes['class'] = '';
        }
        $attributes['class'] = trim('h-captcha ' . $attributes['class']);

        return $attributes;
    }

    /**
     * Build HTML attributes.
     *
     * @param array $attributes
     *
     * @return string
     */
    protected function buildAttributes(array $attributes): string
    {
        $html = [];

        foreach ($attributes as $key => $value) {
            $html[] = $key . '="' . $value . '"';
        }

        return count($html) ? ' ' . implode(' ', $html) : '';
    }

    private function getInvisibleElement($formIdentifier): string
    {
        $functionName = 'onSubmit' . str_replace(['-', '=', '\'', '"', '<', '>', '`'], '', $formIdentifier);

        return '<script>
                     document.addEventListener("DOMContentLoaded", function() {
                        let button = document.getElementsByTagName("button");
                    
                        button[0].setAttribute("data-sitekey", "' . $this->sitekey . '");
                        button[0].setAttribute("data-callback", "'. $functionName .'");
                        button[0].classList.add("h-captcha");
                     })
                    
                    function '. $functionName .'(){
                        document.getElementById("'. $formIdentifier .'").submit();
                    }
                </script>';
    }

    private function getVisibleElement($attributes): string
    {
        $attributes = $this->prepareAttributes($attributes);
        return '<div' . $this->buildAttributes($attributes) . '></div>';
    }
}