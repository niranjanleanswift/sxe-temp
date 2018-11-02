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

namespace LeanSwift\EconnectSXE\Model;

use Magento\Framework\Model\AbstractModel;
use \LeanSwift\EconnectSXE\Model\ResourceModel\Customerprice as rCustomerPrice;
/**
 * Class Customerprice
 * @package LeanSwift\Econnect\Model
 */
class Customerprice extends AbstractModel
{
    /**
     * Event Prefix
     *
     * @var $EventPrefix
     */
    protected $_eventPrefix = 'leanswift_sxe_customerprices';

    /**
     * @var ResourceModel\Customerprice
     */
    protected $_customerPriceModel;

    /**
     * Initialize CustomerPrice model
     *
     * @return void
     */
    public function __construct(rCustomerPrice $customerprice)
    {
        $this->_customerPriceModel = $customerprice;
    }

    /**
     * Get the price for the product
     *
     * @param $productErpNumber
     * @param $customerErpNumber
     * @return null
     */
    public function getSavedCustomerPrice($productErpNumber, $customerErpNumber)
    {
        $price = null;
        if ($customerErpNumber && $productErpNumber) {
            $priceModel = $this->loadByCustomerItem($customerErpNumber, $productErpNumber);
            $price = ($priceModel['price']) ? ($priceModel['price']) : null;
        }

        return $price;
    }

    /**
     * Returns the customer Erp Price
     *
     * @param $customerErpNumber
     * @param $productErpNumber
     * @param null $customerId
     * @return ResourceModel\Array
     */
    public function loadByCustomerItem($customerErpNumber, $productErpNumber, $customerId = null)
    {
        return $this->_customerPriceModel->loadResourceByCustomerItem($customerErpNumber, $productErpNumber, $customerId);
    }

    /**
     * Updates the price into table
     *
     * @param $priceResponseArray
     * @param $customerId
     * @param $customerErpNumber
     * @param $productId
     * @param $facility
     */
    public function updateinTable($priceResponseArray, $customerId, $customerErpNumber, $productId, $facility)
    {
        $customerPriceModel = null;
        $salesPrice = null;
        $productErpNumber = $priceResponseArray['itemNo'];
        $priceExists = $this->loadByCustomerItem($customerErpNumber, $productErpNumber, $customerId);
        $salesPrice = $priceResponseArray['salesPrice'];
        // Proceed only if price is greater than zero
        if ($salesPrice > 0) {
            $data['sxe_customer_nr'] = $customerErpNumber;
            $data['sxe_productno'] = $productErpNumber;
            $data['warehouse'] = $facility;
            $data['price'] = $salesPrice;
            $data['qty'] = $priceResponseArray['qty']?? 1;
            $data['product_id'] = $productId;
            $data['customer_id'] = $customerId;
            $data['last_updated'] = date("Y-m-d H:i:s");
            $this->_customerPriceModel->updateRecord($data);
        }
        else if($priceExists['id'] && !$salesPrice) {
            $this->_customerPriceModel->deleteRecord($customerErpNumber, $productErpNumber, $customerId);
        }
    }
}