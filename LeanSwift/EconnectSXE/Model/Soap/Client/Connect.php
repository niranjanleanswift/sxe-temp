<?php

namespace LeanSwift\EconnectSXE\Model\Soap\Client;

use LeanSwift\EconnectSXE\Helper\Data;

use LeanSwift\EconnectSXE\Api\Soap\RequestInterface;

class Connect implements RequestInterface
{
    protected $_callConnectionParams;
    protected $_helperData;
    protected $_callConnectionString;
    protected $_requestNamespace;
    protected $_requestBody;
    protected $_uri;
    protected $_API;
    protected $_locationURL;
    protected $_connectionRequest;
    protected $_removeString;
    protected $_response;
    protected $_commonFactory;
    protected $_loggerEnablePath = '';

    public function __construct
    (
        $locationURL = '',
        $callConnectionString = '',
        $callConnectionParams = [],
        $removeString = [],
        Data $helper,
        CommonFactory $commonFactory
    )
    {
        $this->_locationURL = $locationURL;
        $this->_callConnectionParams = $callConnectionParams;
        $this->_helperData = $helper;
        $this->_callConnectionString = $callConnectionString;
        $this->_removeString = $removeString;
        $this->_commonFactory = $commonFactory;
    }

    public function setURI($uri) {
        $this->_uri = $uri;
    }

    public function getURI() {
        return $this->_uri? $this->_uri: Data::TEMP_NAMESPACE;
    }

    public function setAPI($api) {
        $this->_API = $api;
    }

    public function getAPI(){
        return $this->_API;
    }

    public function setLocationURL($url) {
        $this->_locationURL = $url;
    }

    public function getLocationURL(){
        return $this->_helperData->getDataValue($this->_locationURL);
    }

    public function setRequestBody($requestBody, $nameSpace) {
        $this->_requestBody = $requestBody;
        $this->_requestNamespace = $nameSpace;
    }

    public function getRequestBody() {
        return $this->_requestBody;
    }

    public function removeString($string= [], $remove=false) {
        if($remove) {
            $this->_removeString = $string;
        }
        else {
            $this->_removeString = array_merge_recursive($this->_removeString, $string);
        }
    }

    public function getRemoveString() {
        return $this->_removeString;
    }

    public function setLogger(\Monolog\Logger $logger) {
        $this->logger = $logger;
    }

    public function setLoggerEnablePath($loggerEnablePath) {
        $this->_loggerEnablePath = $loggerEnablePath;
    }

    public function isLoggerEnabled() {
        return $this->_helperData->getDataValue($this->_loggerEnablePath);
    }

    /**
     * Request to location is formed here
     */

    public function sendRequest()
    {
        try{
            $options['trace'] = 1;
            $options['location'] = $this->getLocationURL();
            $options['uri'] = $this->getURI();
            $client = $this->_commonFactory->create(['options'=>$options]);
            $client->setRemoveString($this->getRemoveString());
            $client->setAPI($this->getAPI());
            $result = $client->__call($this->getAPI(), $this->formRequestBody());
            $json = \Zend_Json::encode($result, true);
            $responseArray = \Zend_Json::decode($json);
            $this->_response = $responseArray;
            if($this->isLoggerEnabled()) {
                $this->logger->info(print_r($client->__getLastRequest(),true));
                $this->logger->info(print_r($client->__getLastResponse(),true));
            }
        }
        catch (\Exception $e) {
            if($this->isLoggerEnabled()) {
                $this->logger->info($e->getMessage());
            }
        }
    }

    public function getResponse(){
        return $this->_response;
    }

    /**
     * Add Request body
     * @return array
     */
    public function addRequest() {
        if(!empty($this->getRequestBody())) {
            $param = '';
            foreach ($this->getRequestBody() as $key=> $value) {
                $param[] = new \SoapVar($value,XSD_ANYTYPE, null, "", $key, $this->_requestNamespace);
            }
            $wrapper = new \SoapVar($param,SOAP_ENC_OBJECT,null, "", null,$this->getURI());
            return [new \SoapParam($wrapper, 'request')];
        }
    }

    /**
     * Form the Connection Parameter
     * @return array
     */
    public function formRequestBody() {
        if(!empty($this->_callConnectionParams)) {
            $param = '';
            foreach ($this->_callConnectionParams as $key=> $value) {
                $namespace = '';
                if(isset($value['namespace'])) {
                    $namespace = $value['namespace'];
                }
                if(isset($value['pathvalue'])) {
                    $param[] = new \SoapVar($this->_helperData->getDataValue($value['pathvalue']),XSD_ANYTYPE, "", "", $key, $namespace);
                }
                elseif(isset($value['value'])) {
                    $param[] = new \SoapVar($value['value'],XSD_ANYTYPE, "", "", $key, $namespace);
                }
                else {
                    continue;
                }
            }
            $wrapper = new \SoapVar($param,SOAP_ENC_OBJECT,"", null, null,$this->getURI());
            $this->_connectionRequest = [new \SoapParam($wrapper,$this->_callConnectionString)];
            return array_merge($this->_connectionRequest, $this->addRequest());
        }
    }
}
