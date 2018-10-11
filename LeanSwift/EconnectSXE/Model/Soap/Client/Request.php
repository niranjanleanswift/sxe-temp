<?php

namespace LeanSwift\EconnectSXE\Model\Soap\Client;

use LeanSwift\EconnectSXE\Helper\Data;

use LeanSwift\EconnectSXE\Api\Soap\RequestInterface;

class Request implements RequestInterface
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

    public function __construct
    (
        $callConnectionString = '',
        $callConnectionParams = [],
        $removeString = [],
        Data $helper
    )
    {
        $this->_callConnectionParams = $callConnectionParams;
        $this->_helperData = $helper;
        $this->_callConnectionString = $callConnectionString;
        $this->_removeString = $removeString;
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
        return $this->_locationURL;
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

    public function sendRequest()
    {
        try{
            $client = new \Zend\Soap\Client\Common([$this, '_doRequest'], null, array(
                'trace' => 1,
                'location' => $this->getLocationURL(),
                'uri' => $this->getURI()
            ));
            $result = $client->
            __call($this->getAPI(), $this->formRequestBody());
//            $result = $client->
//            __call("allConnection",array(10));
            echo "<pre>";
            //echo "REQUEST:\n" .$client->__getLastRequest(). "\n";
            // echo "REQUEST:\n" .
            echo   htmlentities($client->__getLastRequest()) . "\n";
            print $result;
        }
        catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public function _doRequest(\Zend\Soap\Client\Common $client, $request, $location, $action, $version, $oneWay = null)
    {
        $request = $this->removeTypeInElement($request);
        throw new \Exception($request);
        exit;
        // Perform request as is
        if ($oneWay === null) {
            return call_user_func(
                [$client, 'SoapClient::__doRequest'],
                $request,
                $location,
                $action,
                $version
            );
        }
        return call_user_func(
            [$client, 'SoapClient::__doRequest'],
            $request,
            $location,
            $action,
            $version,
            $oneWay
        );
    }

    public function removeTypeInElement($request) {
        $emptyString = $this->getRemoveString();
        if(!empty($emptyString)) {
            foreach ($emptyString['list'] as $key => $value) {
                $request = str_replace($value,"",$request);
            }
        }
        return $request;
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
