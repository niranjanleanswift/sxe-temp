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
use LeanSwift\EconnectSXE\Api\UpdateStock;

class StockSyncOnCheckoutObserver implements ObserverInterface
{

    protected $_request;
    protected $_updateStock;

    public function __construct(
        RequestInterface $RequestInterface,
        UpdateStock $updateStock
    )
    {
        $this->_request = $RequestInterface;
        $this->_updateStock = $updateStock;
    }

    public function execute(Observer $observer)
    {
        $this->_updateStock->prepareCartItemSync();
    }

}