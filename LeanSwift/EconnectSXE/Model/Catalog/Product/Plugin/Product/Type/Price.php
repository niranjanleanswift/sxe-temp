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

namespace LeanSwift\EconnectSXE\Model\Catalog\Product\Plugin\Product\Type;

use LeanSwift\EconnectSXE\Api\PriceInterface;
use Magento\Framework\Registry;

class Price
{
    /**
     * Customer Price Object Interface
     *
     * @var PriceInterface
     */
    protected $_customerPriceInterface;

    protected $_registry;

    /**
     * Price constructor.
     *
     * @param PriceInterface $customerPriceInterface
     */
    public function __construct
    (   PriceInterface $customerPriceInterface,
        Registry $registry
    )
    {
        $this->_registry = $registry;
        $this->_customerPriceInterface = $customerPriceInterface;
    }

    /**
     * It returns the customer price for the product
     *
     * @param ProductData $product
     *
     * @return float|int|null
     */
    public function getCustomerSavedPrice($product)
    {
        return $this->_customerPriceInterface->getCustomerPriceForProduct($product);
    }

    /**
     * Returns the whether the customer price can be viewable or not
     *
     * @return int|bool
     */
    public function canViewCustomerPrice()
    {
        return $this->isCustomerPriceEnabled() && $this->getCustomerErpNumber();
    }

    /**
     *
     *Check whether the custome price is enabled or not
     *
     * @return bool
     */
    public function isCustomerPriceEnabled()
    {
        return $this->_registry->registry('enabled_customer_price');
    }

    /**
     *
     *Get the product Erp Number
     *
     * @return int
     */
    public function getCustomerErpNumber()
    {
        return $this->_customerPriceInterface->getCustomerErpNumber();
    }
}