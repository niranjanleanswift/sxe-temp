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

namespace LeanSwift\EconnectSXE\Model\Catalog\Product\Plugin\Pricing;

use LeanSwift\EconnectSXE\Model\Catalog\Product\Plugin\Product\Type\Price;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;

/**
 * Class FinalPrice
 * @package LeanSwift\Econnect\Model\Catalog\Product\Plugin\Pricing
 */
class FinalPrice extends Price
{
    /**
     * Sets the final price for the product
     *
     * @param CatalogFinalPrice $priceObject
     * @param $finalPrice
     * @return float|int|null
     */
    public function aftergetValue(CatalogFinalPrice $priceObject, $finalPrice)
    {
        if ($this->canViewCustomerPrice()) {
            if ($customerPrice = $this->getCustomerSavedPrice($priceObject->getProduct())) {
                if ($customerPrice > 0) {
                    $finalPrice = $customerPrice;
                }
            }
        }

        return $finalPrice;
    }
}