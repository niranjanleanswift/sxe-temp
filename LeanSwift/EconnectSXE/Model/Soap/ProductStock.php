<?php

namespace LeanSwift\EconnectSXE\Model\Soap;

class ProductStock extends AbstractRequest
{
    public function setPostValues($postValues) {
        $this->_postValues = $postValues;
    }
    public function getPostValues() {
        return $this->_postValues;
    }

    public function send() {
        parent::send();
        //parent::getResponse();
    }
}
