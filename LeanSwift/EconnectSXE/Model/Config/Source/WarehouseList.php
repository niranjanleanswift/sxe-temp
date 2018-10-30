<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LeanSwift\EconnectSXE\Model\Config\Source;

use LeanSwift\EconnectSXE\Model\ResourceModel\Configuration;

class WarehouseList implements \Magento\Framework\Option\ArrayInterface
{

    protected $_rConfiguration;

    public function __construct(
        Configuration $rConfiguration
    )
    {
        $this->_rConfiguration = $rConfiguration;
    }

    public function toOptionArray()
    {
        $output = [];
        $list = $this->_rConfiguration->loadByterm('warehouse');
        if(!empty($list)) {
            foreach ($list as $key=>$keyValue) {
                $output[]  = ['value'=>$keyValue['Code'], 'label'=>$keyValue['Code'].'-'.$keyValue['Description']];
            }
        }
        else {
            $output[]  = ['value'=>'', 'label'=>'Connection Error'];
        }
        return $output;
    }
}
