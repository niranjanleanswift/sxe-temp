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

namespace LeanSwift\EconnectSXE\Api;

/**
 * Interface PriceInterface
 * @package LeanSwift\Econnect\Api
 */
interface PriceInterface
{
    public function setEnablePath($path);

    public function getEnablePath();

    public function getLogger();

    public function canWriteLog();

    /**
     * Check whether the customer price is enabled or not
     *
     * @return mixed
     */
    public function isCustomerPriceEnabled();

    /**
     * Get the product Erp Number
     *
     * @return mixed
     */
    public function getCustomerErpNumber();

    /**
     * Get the store Id
     *
     * @return mixed
     */
    public function getStoreId();

    /**
     * Get the customer price for the product object
     *
     * @param $productObject
     * @return mixed
     */
    public function getCustomerPriceForProduct($productObject);

    /**
     * Get the cache hours for the price sync
     *
     * @param $storeId
     * @return mixed
     */
    public function getCacheHours($storeId);

    /**
     * Update the customer Epr price for the single product
     *
     * @param $product
     * @return mixed
     */
    public function updateCustomerPriceForProduct($product);

    /**
     * Check whether the product or their sub product is in cache or not
     *
     * @param $product
     * @return mixed
     */
    public function getProductsNotInCache($product);

    /**
     * Updating the Product Erp numbers along with customer Erp number
     *
     * @param $productData
     * @param $customerErpNumber
     * @return mixed
     */
    public function updateProductCustomerPrice($productData, $customerErpNumber);

    /**
     * Getting the product Erp numbers and product ids which is not in cache
     *
     * @param $productData
     * @return mixed
     */
    public function checkProductsErpPrice($productData);

    /**
     * Get the Customer Erp Price
     *
     * @param $erpCustomerNr
     * @param $productErpNo
     * @param $qty
     * @param $storeId
     * @return mixed
     */
    public function sendPriceRequest($erpCustomerNr, $productErpNo, $qty, $storeId);

    /**
     * Get Customer Price
     *
     * @param $product
     * @param $customer
     * @param $storeId
     * @return mixed
     */
    public function getCustomerPrice($product, $customer, $storeId);
}