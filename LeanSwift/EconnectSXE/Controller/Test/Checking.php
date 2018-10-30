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

namespace LeanSwift\EconnectSXE\Controller\Test;

use Klarna\Core\Exception;
use Magento\Framework\App\Action\Action;
use LeanSwift\EconnectSXE\Helper\Data;
use Magento\Framework\App\Action\Context;
class Checking extends Action
{
    protected $_logger;

    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    public function execute()
    {
        echo "<pre>";
        $WarehouseList = $this->_objectManager->create('LeanSwift\EconnectSXE\Model\Config\Source\WarehouseList');
        print_r($WarehouseList->toOptionArray());
        echo "done";
        exit;
        exit;

        try{
            $product = $this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            //
            $info = $product->getById(1,false,1,true);
            echo  $info->getSku();
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }

        exit;
        $StockRegistryInterface->update(1,'TEST SIMPLE PRODUCT');
        $StockItemInterface = $this->_objectManager->create('Magento\CatalogInventory\Api\Data\StockItemInterface');
        $StockItemInterface->setProductId(1)
            ->setQty(120);
        $StockRegistryInterface->updateStockItemBySku('TEST SIMPLE PRODUCT', $StockItemInterface);
        echo "done";
        exit;
        //$WarehouseList->send();

        try{
//            $values['CustomerNumber'] ='100';
//            $values['Product'] ='1-001';
//            $values['UseCrossReferenceFlag'] = 0;
//            //$SecurityInfoInterface = $this->_objectManager->create('Magento\Framework\Url\SecurityInfoInterface');
//            //$SecurityInfoInterface->isSecure(123);
//
//            $demo = $this->_objectManager->create('LeanSwift\EconnectSXE\Api\Soap\RequestInterface');
//            //$demo->setURI(Data::TEMP_NAMESPACE);
//            //$data['list'] = ['Demo2'=>'Demo'];
//            //$demo->removeString($data);
//            $demo->setLocationURL('http://192.168.111.55/sxapi/Service.svc');
//            $demo->setRequestBody($values, Data::PRODUCT_NAMESPACE);
//            $demo->setAPI(Data::SOAP_PRODUCT_API);
//            $demo->sendRequest();


            //warehouse

            $WarehouseList = $this->_objectManager->create('LeanSwift\EconnectSXE\Model\Soap\WarehouseList');
            $WarehouseList->send();
            exit;
            $values['Sort'] ='';
            //$SecurityInfoInterface = $this->_objectManager->create('Magento\Framework\Url\SecurityInfoInterface');
            //$SecurityInfoInterface->isSecure(123);

            $demo = $this->_objectManager->create('LeanSwift\EconnectSXE\Api\Soap\RequestInterface');
            //$demo->setURI(Data::TEMP_NAMESPACE);
            //$data['list'] = ['Demo2'=>'Demo'];
            //$demo->removeString($data);
            $demo->setLocationURL('http://192.168.111.55/sxapi/Service.svc');
            $demo->setRequestBody($values, Data::WAREHOUSE_NAMESPACE);
            $demo->setAPI(Data::SOAP_WAREHOUSE_API);
            $demo->sendRequest();

            echo "sended";
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function demo() {
        if ($this->_LockManagement->isLocked('demo')) {
            echo "locked";
            echo "<br>";
        }
        else {
            echo "Unlocked";
            echo "</br>";
            echo "<br>";
            $this->_LockManagement->unlock('demo');
        }
    }
}
