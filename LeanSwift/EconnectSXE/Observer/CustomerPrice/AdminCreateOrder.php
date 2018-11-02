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
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Header as OrderHead;

/**
 * Class AdminCreateOrder
 * @package LeanSwift\Econnect\Observer\CustomerPrice
 */
class AdminCreateOrder implements ObserverInterface
{
    /**
     * Econnect Data helper
     *
     * @var LeanSwift\Econnect\Helper\Data
     */
    protected $_helper;

    /**
     * @var ProductFactory
     */
    protected $_productfactory;

    /**
     * @var OrderHead
     */
    protected $_orderHead;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Econnect product stock
     *
     * @var PriceInterface|null
     */
    protected $_priceInterface = null;

    /**
     * AdminCreateOrder constructor.
     * @param Data $helperData
     * @param OrderHead $orderhead
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     * @param PriceInterface $priceInterface
     */
    public function __construct(
        Data $helperData,
        OrderHead $orderhead,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory,
        PriceInterface $priceInterface
    )
    {
        $this->_priceInterface = $priceInterface;
        $this->_helperData = $helperData;
        $this->_orderHead = $orderhead;
        $this->_customerFactory = $customerFactory;
        $this->_productfactory = $productFactory;
    }

    /**
     * Update Customer Price
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $isEnable = $this->_helperData->isEnableAdmin();
        if ($isEnable) {
            $storeId = 0;
            $flag = false;
            $items = $observer->getItems();
            if (!empty($items)) {
                $productObject = $this->_productFactory->create();
                $customerObject = $this->_customerFactory->create();
                foreach ($items as $quote_item) {
                    $productType = $quote_item->getProductType();
                    if ($productType == 'configurable') {
                        $flag = true;
                        $configQuote = $quote_item;
                        continue;
                    }
                    $productId = $quote_item->getProductId();
                    $product = $productObject->load($productId);
                    $erpItemNumber = $product->getErpItemNumber();
                    if ($erpItemNumber) {
                        $customerId = $this->_orderHead->getCustomerId();
                        $customer = $customerObject->load($customerId);
                        $erpPrice = $this->_priceInterface->getCustomerPrice($product, $customer, $storeId);
                        if ($erpPrice) {
                            if ($flag) {
                                $configQuote->setCustomPrice($erpPrice)->setOriginalCustomPrice($erpPrice);
                            }
                            $quote_item->setCustomPrice($erpPrice)->setOriginalCustomPrice($erpPrice);
                        }

                    }

                }
            }
        }
    }
}