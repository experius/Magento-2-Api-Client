<?php

namespace Experius\Magento2ApiClient\Service;

/**
 * Class RestApi
 *
 * @package Experius\Magento2ApiClient\Service
 */
class RestApi
{

    /**
     * @var
     */
    protected $token;
    /**
     * @var string
     */
    protected $storeCode = 'all';
    /**
     * @var array
     */
    protected $extraHeaders = [];
    /**
     * @var
     */
    protected $headers;
    /**
     * @var
     */
    protected $apiCallUrl;
    /**
     * @var
     */
    protected $url;
    /**
     * @var
     */
    protected $username;
    /**
     * @var
     */
    protected $password;
    /**
     * @var
     */
    protected $apiKey;


    /**
     * @throws \Exception
     */
    public function init()
    {
        if ($this->validateConfig()) {
            $this->getToken();
        }
    }

    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->storeCode;
    }

    /**
     * @param $storeCode
     */
    public function setStoreCode($storeCode)
    {
        $this->storeCode = $storeCode;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        $storeCode = '';
        if ($this->getStoreCode() != '') {
            $storeCode = $this->getStoreCode() . '/';
        }
        return str_replace('%storecode/', $storeCode, $this->url);
    }

    /**
     * Accepted: admin or customer.
     *
     * @param string $integration
     * @return bool|mixed
     * @throws \Exception
     */
    protected function getToken($integration)
    {
        $integrations = ['admin', 'customer'];

        if (!$this->token && in_array($integration, $integrations)) {
            $data = array("username" => $this->getUsername(), "password" => $this->getPassword());
            if ($token = $this->call("integration/$integration/token", $data, "POST")) {
                $this->token = $token;
            }
        }
        return $this->token;
    }

    /**
     * @return array
     */
    protected function getDefaultHeaders()
    {
        $headers = [];
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
            $headers['realtime-stock'] = 'Disable-RealtimeStock: true';
        }
        $headers = array_merge($headers, $this->extraHeaders);
        return $headers;
    }

    /**
     * @param $url
     * @param array $dataArray
     * @param string $postType
     * @param string $storeCode
     * @param array $extraHeaders
     * @return bool|mixed
     * @throws \Exception
     */
    public function call($url, $dataArray = array(), $postType = "GET", $storeCode = 'all', $extraHeaders = [])
    {
        $this->extraHeaders = $extraHeaders;
        $this->storeCode = $storeCode;
        $handle = curl_init();
        $this->apiCallUrl = trim($this->getUrl(), '/') . '/' . ltrim($url, '/');
        $this->headers = $this->getDefaultHeaders();

        switch ($postType) {
            case 'GET':
                $this->buildGetCall($handle, $dataArray);
                break;
            case 'POST':
                $this->buildPostCall($handle, $dataArray);
                break;
            case 'PUT':
                $this->buildPutCall($handle, $dataArray);
                break;
            case 'DELETE':
                $this->buildDeleteCall($handle, $dataArray);
                break;
        }
        curl_setopt($handle, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($handle, CURLOPT_URL, $this->apiCallUrl);

        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);

        $this->setDefaultOptions($handle);
        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $response = array('response' => $response, 'code' => $code);
        $code = $response['code'];
        $response = $response['response'];

        if ($code == '200') {
            return json_decode($response);
        } elseif ($code == '202') {
            return true;
        } elseif ((int)$code >= 300) {
            if ($decodedResponse = json_decode($response)) {
                $exception = $code . " - Error making request to server: " . $decodedResponse->message;
                if (isset($decodedResponse->parameters)) {
                    $parameters = (is_object($decodedResponse->parameters)) ? json_encode($decodedResponse->parameters) : $decodedResponse->parameters;
                    $exception = "{$code} - Error making request to server: {$decodedResponse->message} - {$parameters}";
                }
                throw new \Exception($exception, $code);
            }
            throw new \Exception($code . " - Error making request to server:\n" . $response, $code);
        }
        throw new \Exception($code . " - Error making request to server no valid response:\n" . $response, $code);
    }


    /**
     * @return bool
     * @throws \Exception
     */
    protected function validateConfig()
    {
        $missingConfig = array();
        if (!$this->getUsername()) {
            $missingConfig[] = 'username';
        } elseif (!$this->getPassword()) {
            $missingConfig[] = 'password';
        } elseif (!$this->getUrl()) {
            $missingConfig[] = 'url';
        }
        if (!empty($missingConfig)) {
            $missingConfigString = implode(', ', $missingConfig);
            throw new \Exception("One or more config values are missing: {$missingConfigString}");
        }
        return true;
    }


    /**
     * @param $handle
     */
    protected function setDefaultOptions($handle)
    {
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
    }

    /**
     * @param $handle
     * @param $dataArray
     */
    protected function buildPostCall($handle, $dataArray)
    {
        $dataJson = json_encode($dataArray);
        switch (json_last_error()) {
            case JSON_ERROR_UTF8:
                $dataArray = $this->utf8ize($dataArray);
                $dataJson = json_encode($dataArray);
                break;
        }
        curl_setopt($handle, CURLOPT_POSTFIELDS, $dataJson);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
        $this->headers[] = 'Content-Type: application/json';
        $this->headers[] = 'Content-Length: ' . strlen($dataJson);
    }

    /**
     * @param $handle
     * @param $dataArray
     */
    protected function buildPutCall($handle, $dataArray)
    {
        $this->buildPostCall($handle, $dataArray);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
    }

    /**
     * @param $handle
     * @param $dataArray
     */
    protected function buildDeleteCall($handle, $dataArray)
    {
        $this->buildPostCall($handle, $dataArray);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    /**
     * @param $handle
     * @param $dataArray
     */
    protected function buildGetCall($handle, $dataArray)
    {
        if (!empty($dataArray)) {
            $urlParameters = http_build_query($dataArray);
            $this->apiCallUrl .= '?' . $urlParameters;
        }
    }

    /**
     * @param $mixed
     * @return array|string
     */
    protected function utf8ize($mixed)
    {
        if (is_array($mixed) || is_object($mixed)) {
            foreach ($mixed as $key => $value) {
                if (is_array($mixed)) {
                    $mixed[$key] = $this->utf8ize($value);
                } else {
                    $mixed->$key = $this->utf8ize($value);
                }
            }
        } elseif (is_string($mixed)) {
            return utf8_encode($mixed);
        }
        return $mixed;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param $apiKey
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getPassword()
    {
        return $this->password;
    }

}
