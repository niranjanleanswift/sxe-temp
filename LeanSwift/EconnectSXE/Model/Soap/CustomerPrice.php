<?php

namespace LeanSwift\EconnectSXE\Model\Soap;

class CustomerPrice extends AbstractRequest
{
    public function setPostValues($postValues) {
        $this->_postValues = $postValues;
    }

    public function getPostValues() {
        return $this->_postValues;
        $data['CustomerNumber'] = '151';
        $data['ProductCode'] = '1-002';
        $data['Quantity'] = '15';
        $data['Warehouse'] = 'main';
        return $data;
    }
}
