<?php

namespace LeanSwift\EconnectSXE\Model\Soap;

class CustomerPrice extends AbstractRequest
{
    public function setPostValues($postValues) {
        $this->_postValues = $postValues;
    }

    public function getPostValues() {
        $data['CustomerNumber'] = '151';
        $data['ProductCode'] = '1-002';
        $data['Quantity'] = '15';
        $data['Warehouse'] = 'main';
        return $data;
    }
}
