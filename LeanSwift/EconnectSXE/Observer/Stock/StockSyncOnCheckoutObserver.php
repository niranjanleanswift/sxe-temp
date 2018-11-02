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
 * @copyright   Copyright (c) 2018 LeanSwift Inc. (http://www.leanswift.com)
 * @license     http://www.leanswift.com/license/connector-extension
 */

namespace LeanSwift\EconnectSXE\Observer\Stock;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use LeanSwift\EconnectSXE\Api\StockInterface;
use LeanSwift\EconnectSXE\Helper\Data;

class StockSyncOnCheckoutObserver implements ObserverInterface
{

    protected $_request;
    protected $_productStock;
    protected $_helperData = null;

    public function __construct(
        RequestInterface $RequestInterface,
        StockInterface $updateStock,
        Data $helperData
    )
    {
        $this->_request = $RequestInterface;
        $this->_productStock = $updateStock;
        $this->_helperData = $helperData;
    }

    public function execute(Observer $observer)
    {
        $path = $this->_productStock->getEnablePath();
        $storeId = $this->_helperData->getStoreId();
        $isSyncEnabled = $this->_helperData->getDataValue($path, $storeId);
        if ($isSyncEnabled) {
            $this->_productStock->prepareCartItemSync();
        }
    }

}