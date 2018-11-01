<?php
/**
 * LeanSwift eConnect Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the LeanSwift eConnect Extension License
 * that is bundled with this package in the file LICENSE.txt located in the Connector Server.
 *
 * DISCLAIMER
 *
 * This extension is licensed and distributed by LeanSwift. Do not edit or add to this file
 * if you wish to upgrade Extension and Connector to newer versions in the future.
 * If you wish to customize Extension for your needs please contact LeanSwift for more
 * information. You may not reverse engineer, decompile,
 * or disassemble LeanSwift Connector Extension (All Versions), except and only to the extent that
 * such activity is expressly permitted by applicable law not withstanding this limitation.
 *
 * @copyright   Copyright (c) 2015 LeanSwift Inc. (http://www.leanswift.com)
 * @license     http://www.leanswift.com/license/connector-extension
 */

namespace LeanSwift\EconnectSXE\Observer\Stock;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use LeanSwift\EconnectSXE\Api\UpdateStock;

class StockSyncOnProductViewObserver implements ObserverInterface
{
    protected $_updateStock;
    protected $_request;

    public function __construct(
        UpdateStock $updateStock,
        RequestInterface $request
    )
    {
        $this->_request = $request;
        $this->_updateStock = $updateStock;
    }

    public function execute(Observer $observer)
    {
        $requestParams = $this->_request->getParams();
        if (isset($requestParams['selected_configurable_option']) && $requestParams['selected_configurable_option'] != null) {
            $productId = (int)$requestParams['selected_configurable_option']; //Get associated product from configurable product
            //$this->_productStock->updateProductStock($productId); //Update product stock based on M3 response
            $this->_updateStock->updateProductStock($productId);
        } else if (isset($requestParams['super_group']) && $requestParams['super_group'] != null) {
            //Get associated product Ids whose qty's greater than zero when add to cart
            $productIds = array_filter($requestParams['super_group'], function ($qty) {
                return $qty > 0;
            });
            if (count($productIds)) {
                $this->_updateStock->prepareGroupedProductStock($productIds);
            }
        } else {
            $productId = (int)$this->_request->getParam('id');
            $this->_updateStock->updateProductStock($productId); //Update product stock based on M3 response
        }
    }
}