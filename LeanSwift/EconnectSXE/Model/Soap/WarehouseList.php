<?php

namespace LeanSwift\EconnectSXE\Model\Soap;

class WarehouseList extends AbstractRequest
{
    public function getPostValues() {
        $values['sort'] = '';
        return $values;
    }
}
