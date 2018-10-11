<?php

namespace LeanSwift\EconnectSXE\Model\Soap;

class ProductQty extends AbstractRequest
{
    public function getPostValues() {
        $values['CustomerNumber'] ='100';
        $values['Product'] ='1-001';
        $values['UseCrossReferenceFlag'] = 0;
        return $values;
    }
}
