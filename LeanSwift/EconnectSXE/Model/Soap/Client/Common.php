<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace LeanSwift\EconnectSXE\Model\Soap\Client;

use LeanSwift\EconnectSXE\Helper\Data;
use SoapClient;
use Zend\Uri\Http as HttpUri;

class Common extends SoapClient
{
    /**
     * doRequest() pre-processing method
     *
     * @var callable
     */
    protected $doRequestCallback;

    protected $curlClient;
    protected $_removeString;
    protected $URI;


    public function __construct($options)
    {
        parent::__construct(null, $options);
    }

    /**
     * Performs SOAP request over HTTP.
     * Overridden to implement different transport layers, perform additional
     * XML processing or other purpose.
     *
     * @param  string $request
     * @param  string $location
     * @param  string $action
     * @param  int    $version
     * @param  int    $oneWay
     * @return mixed
     */


    public function setRemoveString($string) {
        $this->_removeString = $string;
    }

    public function __getLastRequestHeaders()
    {
        return $this->__last_request_headers;
    }

    public function setAPI($uri) {
        $this->URI = $uri;
    }

    public function __doRequest($request, $location, $action, $version, $oneWay = null)
    {
        $uri = new HttpUri($location);
        $headers = $this->buildHeaders($action, $uri);
        $this->__last_request = $this->removeTypeInElement($request);
        $request = $this->__last_request;
        $this->__last_request_headers = $headers;
        //Hack fix to change the action
        $action = str_replace($this->URI, Data::ISERVICE_API.$this->URI,$action);
        $action = str_replace("#", '',$action);
        if ($oneWay === null) {
            return call_user_func(
                [$this,'SoapClient::__doRequest'],
                $request,
                $location,
                $action,
                $version
            );
        }
        else {
            return call_user_func(
                [$this, 'SoapClient::__doRequest'],
                $request,
                $location,
                $action,
                $version,
                $oneWay
            );
        }
    }

    public function removeTypeInElement($request) {
        $emptyString = $this->_removeString;
        if(!empty($emptyString)) {
            foreach ($emptyString['list'] as $key => $value) {
                $request = str_replace($value,"",$request);
            }
        }
        return $request;
    }

    protected function buildHeaders($action, $uri)
    {
        return [
            'Content-Type' => 'text/xml; charset=utf-8',
            'Method'       => 'POST',
            'SOAPAction'   => '"' . $action . '"',
            'User-Agent'   => 'Apache-HttpClient/4.1.1 (java 1.5)',
            'Connection' => 'Keep-Alive',
            'Host'=> $uri->getHost()
        ];
    }

}
