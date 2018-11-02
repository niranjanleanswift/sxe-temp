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

namespace LeanSwift\Econnect\Observer\CustomerPrice;

use LeanSwift\Econnect\Helper\Data;
use LeanSwift\Econnect\Api\PriceInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class BindCustomerPriceOnAddToCart
 * @package LeanSwift\Econnect\Observer\CustomerPrice
 */
class BindCustomerPriceOnAddToCart implements ObserverInterface
{
    /**
     * Econnect product stock
     *
     * @var LeanSwift\Econnect\Api\PriceInterface
     */
    protected $_priceInterface = null;

    /**
     * Econnect helper
     *
     * @var LeanSwift\Econnect\Helper\Data
     */
    protected $_helperData = null;

    /**
     * BindCustomerPriceOnAddToCart constructor.
     * @param Data $helperData
     * @param PriceInterface $priceInterface
     */
    public function __construct(Data $helperData, PriceInterface $priceInterface)
    {
        $this->_priceInterface = $priceInterface;
        $this->_helperData = $helperData;
    }

    /**
     * Check and update product price while adding product to cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->_helperData->getStoreId();
        $isEnabledPriceSync = $this->_priceInterface->isCustomerPriceEnabled($storeId);
        $erpCustomerNr = $this->_priceInterface->getCustomerErpNumber();
        if ($isEnabledPriceSync && $erpCustomerNr) {
            $erpPrice = null;
            $items = $observer->getEvent()->getItems();
            if (is_array($items)) {
                foreach ($items as $item) {
                    if ($option = $item->getOptionByCode('simple_product')) {
                        $product = $option->getProduct();
                    } else {
                        $product = $item->getProduct();
                    }
                    $erpPrice = $this->_priceInterface->getCustomerPriceForProduct($product);

                    if ($erpPrice && $erpPrice > 0) {
                        $item->setCustomPrice($erpPrice)->setOriginalCustomPrice($erpPrice);
                    }
                }
            }
        }
    }
}