<?php

namespace LeanSwift\EconnectSXE\Cron;

use LeanSwift\EconnectSXE\Model\Soap\WarehouseList;
use LeanSwift\EconnectSXE\Model\ResourceModel\Configuration;

class FetchWarehouseList
{
    protected $_warehouse;
    protected $_rConfiguration;

    public function __construct(
        WarehouseList $WarehouseList,
        Configuration $rConfiguration
    )
    {
        $this->_warehouse = $WarehouseList;
        $this->_rConfiguration = $rConfiguration;
    }

    public function fetchValues() {
        $warehouseRequestObject = $this->_warehouse;
        $warehouseRequestObject->send();
        $responseList = $warehouseRequestObject->getResponse();
        if(!empty($responseList) && !$responseList['ErrorMessage']) {
            $data['term'] = 'warehouse';
            $data['response'] = $responseList['Outwarehouse']['Outwarehouse'];
            $this->_rConfiguration->insertConfiguration($data);
        }
    }
}
