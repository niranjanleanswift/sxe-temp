<?php

namespace LeanSwift\EconnectSXE\Api\Soap;

interface RequestInterface
{
    public function setURI($uri);

    public function getURI();

    public function setLocationURL($url);

    public function getLocationURL();

    public function setAPI($requestString);

    public function getAPI();

    public function removeString($string, $remove=false);

    public function getRemoveString();

    public function setRequestBody($values, $nameSpace);

    public function getRequestBody();

    public function sendRequest();
}
