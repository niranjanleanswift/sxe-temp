<?php
/**
 * *
 *  * LeanSwift Extension
 *  *
 *  * NOTICE OF LICENSE
 *  *
 *  * This source file is subject to the LeanSwift Connector Extension License
 *  * that is bundled with this package in the file LICENSE.txt located in the Connector Server.
 *  *
 *  * DISCLAIMER
 *  *
 *  * This extension is licensed and distributed by LeanSwift. Do not edit or add to this file
 *  * if you wish to upgrade Extension and Connector to newer versions in the future.
 *  * If you wish to customize Extension for your needs please contact LeanSwift for more
 *  * information. You may not reverse engineer, decompile,
 *  * or disassemble LeanSwift Connector Extension (All Versions), except and only to the extent that
 *  * such activity is expressly permitted by applicable law not withstanding this limitation.
 *  *
 *  * @copyright   Copyright (C) Leanswift Solutions, Inc - All Rights Reserved
 *  * Unauthorized copying of this file, via any medium is strictly prohibited.
 *  * Proprietary and confidential.
 *  * Terms and conditions http://leanswift.com/leanswift-eula/
 *  * @category Norsea
 *
 */

namespace LeanSwift\EconnectSXE\Controller\Adminhtml\Retrieve;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use LeanSwift\EconnectSXE\Model\Soap\WarehouseList as requestModel;
use LeanSwift\EconnectSXE\Model\ResourceModel\Configuration;
use LeanSwift\EconnectSXE\Model\Config\Source\WarehouseList as OptionList;
use Magento\Framework\Json\Helper\Data;

class Warehouselist extends Action
{
    protected $_logger;
    protected $_warehouseList;
    protected $_optionList;
    protected $_jsonHelper;

    public function __construct(
        Context $context,
        requestModel $WarehouseList,
        Configuration $rConfiguration,
        OptionList $optionList,
        Data $jsonHelper
    )
    {
        $this->_warehouse = $WarehouseList;
        $this->_rConfiguration = $rConfiguration;
        $this->_optionList = $optionList;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $warehouseRequestObject = $this->_warehouse;
        $warehouseRequestObject->send();
        $responseList = $warehouseRequestObject->getResponse();
        if(!empty($responseList) && !$responseList['ErrorMessage']) {
            $data['term'] = 'warehouse';
            $data['response'] = $responseList['Outwarehouse']['Outwarehouse'];
            $this->_rConfiguration->insertConfiguration($data);
        }
        $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($this->_optionList->toOptionArray()));
    }
}
