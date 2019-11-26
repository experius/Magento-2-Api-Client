<?php

namespace Experius\Magento2ApiClient\Service;

/**
 * Class RestApi
 *
 * @package Experius\Magento2ApiClient\Service
 */
class SoapApi
{
    /**
     * @var
     */
    protected $token;
    /**
     * @var
     */
    protected $username;
    /**
     * @var
     */
    protected $password;
    /**
     * @var string
     */
    protected $storeCode = 'all';
    /**
     * @var
     */
    protected $url;
    /**
     * @var
     */
    protected $options;

    /**
     * @return mixed
     */
    public function getOptions()
    {
        if ($this->options && !$this->token) {
            return $this->options;
        }

        $options = [
            "soap_version" => SOAP_1_2,
            "stream_context" => $this->getContext(),
            'cache_wsdl' => WSDL_CACHE_NONE
        ];

        $this->options = $options;

        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

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
     * @return array
     */
    public function getContext()
    {
        $context = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        if ($this->token) {
            $context['http'] = [
                'header' => 'Authorization: Bearer '.$this->token
            ];
        }

        return stream_context_create($context);
    }

    /**
     * @return string
     */
    protected function getToken()
    {
        if (!$this->token) {
            $request = new \SoapClient(
                $this->url . "integrationAdminTokenServiceV1",
                $this->getOptions()
            );

            $token = $request->integrationAdminTokenServiceV1CreateAdminAccessToken(
                array(
                    "username" => $this->getUsername(),
                    "password" => $this->getPassword()
                )
            );

            $this->token = $token->result;
        }
        return $this->token;
    }

    /**
     * @param $storeCode
     */
    public function setStoreCode($storeCode)
    {
        $this->storeCode = $storeCode;
    }

    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->storeCode;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        $this->url .= '/soap/'. $this->getStoreCode() .'?wsdl&services=';
        return $this->url;
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

    /**
     * @param $wsdlEndPoint
     * @return \SoapClient
     * @throws \SoapFault
     */
    public function call($wsdlEndPoint, $method, $data)
    {
        $wsdlUrl = $this->getUrl() . $wsdlEndPoint;

        $soapClient = new \SoapClient(
            $wsdlUrl,
            $this->getOptions()
        );

        $result = $soapClient->$method($data);

        return $result;
    }

}