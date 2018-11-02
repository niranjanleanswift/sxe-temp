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

namespace LeanSwift\EconnectSXE\Model\Catalog\Product;

use LeanSwift\EconnectSXE\Api\PriceInterface;
use LeanSwift\EconnectSXE\Helper\Customer;
use LeanSwift\EconnectSXE\Helper\Product;
use LeanSwift\EconnectSXE\Model\Customerprice;
use LeanSwift\EconnectSXE\Model\Soap\AbstractRequest;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\StoreManagerInterface;
use LeanSwift\EconnectSXE\Helper\Data;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Price
 * @package LeanSwift\Econnect\Model\Catalog\Product
 */
class Price implements PriceInterface
{

    /**
     * Default Cache Hours
     */
    const DEFAULT_CACHE_HOURS = 8;

    /**
     * Econnect Data Helper
     *
     * @var LeanSwift\Econnect\Helper\Data
     */
    protected $_helper;

    /**
     * Econnect Erpapi Helper
     *
     * var LeanSwift\Econnect\Helper\Erpapi
     */
    protected $_erpHelper;

    /**
     * Econnect Product Helper
     *
     * @var ProductHelper
     */
    protected $_productHelper;

    /**
     * Magento Product Object
     *
     * @var Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * LeanSwift M3 Connection
     *
     * @var LeanSwift\Econnect\Model\Econnect\Api\Request
     */
    protected $_request;

    /**
     * Store Manager Object
     *
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core Registry Object
     *
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Configurable Type Object
     *
     * @var Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_configurable;

    /**
     * Grouped Type Object
     *
     * @var Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $_grouped;

    /**
     * Checkout Model Session Object
     *
     * @var Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Customer Price Object
     *
     * @var LeanSwift\Econnect\Model\Customerprice
     */
    protected $_priceModel;

    protected $_warehousePath;

    protected $_responseField;

    protected $_cacheHours;

    protected $_customerSession;

    /**
     * Price constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ProductFactory $product
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param Erpapi $erpapi
     * @param ProductHelper $productHelper
     * @param Request $request
     * @param Grouped $grouped
     * @param CheckoutSession $checkoutSession
     * @param Customerprice $customerPrice
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductFactory $product,
        StoreManagerInterface $storeManager,
        Data $helper,
        AbstractRequest $request,
        Grouped $grouped,
        CheckoutSession $checkoutSession,
        Customerprice $customerPrice,
        $warehousePath = '',
        $responseField = '',
        $CacheHours = '',
        $backendEnablePath = '',
        CustomerSession $session
    )
    {
        $this->_helper = $helper;
        $this->_product = $product;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->_grouped = $grouped;
        $this->_checkoutSession = $checkoutSession;
        $this->_priceModel = $customerPrice;
        $this->_warehousePath = $warehousePath;
        $this->_responseField = $responseField;
        $this->_canWriteLog =  $this->logEnabled();
        $this->_logger = $this->_request->getLogger();
        $this->_cacheHours = $CacheHours;
        $this->_path = $backendEnablePath;
        $this->_customerSession = $session;
    }


    public function setEnablePath($path)
    {
        $this->_path = $path;
    }

    public function getLogger() {
        return $this->_logger;
    }

    public function getEnablePath() {
        return $this->_path;
    }

    public function logEnabled() {
        $this->_helper->getDataValue($this->_request->getLoggerEnablePath(), $this->getStoreId());
    }

    public function canWriteLog() {
        return $this->_canWriteLog;
    }

    /**
     * Get the current Product Object
     *
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Check whether the customer price is enabled
     *
     * @return bool|mixed
     */
    public function isCustomerPriceEnabled()
    {
        $helper = $this->getHelper();
        return ($this->getHelper()->getStoreConfig($helper::XML_PATH_PRICE_ENABLE, null, $this->getStoreId()) && $this->getSession()->IsLoggedIn());
    }

    /**
     * Helper Object
     *
     * @return Data|LeanSwift\Econnect\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Get customer price for the product
     *
     * @param \LeanSwift\Econnect\Api\ProductData $product
     * @return float|int|mixed|null
     */
    public function getCustomerPriceForProduct($product)
    {
        $customerErpNumber = $this->getCustomerErpNumber();
        $price = null;
        $simpleProductErpNumbers = array();
        $storeId = $this->_helper->getStoreId();
        if ($customerErpNumber) {
            $erpItemArray = null;
            $typeId = $product->getTypeId();
            switch ($typeId) {
                case Configurable::TYPE_CODE:
                    $associatedProducts = $product->getTypeInstance()->getUsedProductCollection($product)
                        ->setFlag('has_stock_status_filter', false)
                        ->addStoreFilter($storeId)
                        ->addAttributeToSelect(Product::SXE_PRODUCT_NUMBER);

                    $erpItemArray = $associatedProducts->getColumnValues(Product::SXE_PRODUCT_NUMBER);
                    $erpItemArray = array_map('trim', $erpItemArray);
                    break;
                case Grouped::TYPE_CODE:
                    //There is no separate price for grouped product, only associated products contains price
                    break;
                default:
                    $erpProductNumber = $this->getProductErpNumber($product);
                    if ($erpProductNumber) {
                        $erpItemArray[] = trim($erpProductNumber);
                    }
            }

            if (count($erpItemArray)) {
                $price = $this->getMinimumPrice($customerErpNumber, $erpItemArray);
            }
        }

        return $price;
    }

    /**
     * Get Customer Erp Number
     *
     * @return int|mixed
     */
    public function getCustomerErpNumber()
    {
        $customer = $this->getCustomer();
        return Customer::getCustomerNumber($customer);
    }

    /**
     * Get the product Erp Number
     *
     * @param $product
     * @return mixed
     */
    public function getProductErpNumber($product)
    {
        $product =  $this->_product->create()->load($product->getId());
        return Product::getSXEProductNumber($product);
    }

    /**
     * @param $customerErpNumber
     * @param $productErpNumbers
     *
     * @param $customerErpNumber
     * @param $productErpNumbers
     * @return mixed|null
     */
    public function getMinimumPrice($customerErpNumber, $productErpNumbers)
    {
        $minimumPrice = null;
        $priceArray = [];

        foreach ($productErpNumbers as $productErpNumber) {
            $result = $this->_priceModel->loadByCustomerItem($customerErpNumber, $productErpNumber);
            if ($result) {
                $priceArray[] = $result['price'];
            }
        }

        if ($priceArray) {
            $minimumPrice = min($priceArray);
        }

        return $minimumPrice;
    }

    /**
     * Update the customer Erp price for the single product
     *
     * @param \LeanSwift\Econnect\Api\ProductData $product
     * @return mixed|void
     */
    public function updateCustomerPriceForProduct($product)
    {
        $customerEprNumber = $this->getCustomerErpNumber();
        if ($customerEprNumber) {
            $result = $this->getProductsNotInCache($product);
            if (count($result)) {
                $this->updateProductCustomerPrice($result, $customerEprNumber);
            }
        }
    }

    /**
     * @param \LeanSwift\Econnect\Model\Customer\productData $product
     *
     * @param \LeanSwift\Econnect\Api\productData $product
     * @return array|\LeanSwift\Econnect\Api\Array|mixed
     */
    public function getProductsNotInCache($product)
    {
        $productInfo = [];
        $simpleProductErpNumbers = array();
        $typeId = $product->getTypeId();
        $storeId = $this->_helper->getStoreId();
        switch ($typeId) {
            case Configurable::TYPE_CODE:
                $associatedProducts = $product->getTypeInstance()->getUsedProductCollection($product)
                    ->setFlag('has_stock_status_filter', false)
                    ->addStoreFilter($storeId)
                    ->addAttributeToSelect(Product::SXE_PRODUCT_NUMBER);
                foreach ($associatedProducts as $simpleProduct) {
                    $simpleItemNumber = trim($simpleProduct[Product::SXE_PRODUCT_NUMBER]);
                    if ($simpleItemNumber) {
                        $simpleProductErpNumbers[$simpleProduct['entity_id']] = $simpleItemNumber;
                    }
                }

                break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                $associatedProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $associatedProducts = $this->getHelper()->getAssociatedProductsCollection($associatedProductIds);
                if ($associatedProducts) {
                    foreach ($associatedProducts as $simpleProduct) {
                        $simpleItemNumber = trim($simpleProduct[Product::SXE_PRODUCT_NUMBER]);
                        if ($simpleItemNumber) {
                            $simpleProductErpNumbers[$simpleProduct['entity_id']] = $simpleItemNumber;
                        }
                    }
                }

                break;
            default:
                $erpProductNumber = $this->getProductErpNumber($product);
                if ($erpProductNumber) {
                    $simpleProductErpNumbers[$product->getId()] = $erpProductNumber;
                }
        }

        if (count($simpleProductErpNumbers)) {
            $results = $this->checkProductsErpPrice($simpleProductErpNumbers);
            if (count($results)) {
                foreach ($results as $result) {
                    $productInfo[$result['erp_number']] = $result['product_ids'];
                }
            }
        }

        return $productInfo;
    }

    /**
     * @param int|\LeanSwift\Econnect\Model\Customer\Array $productInfo
     *
     * @param int|\LeanSwift\Econnect\Api\Array $productInfo
     * @return array|\LeanSwift\Econnect\Api\Array|mixed
     */
    public function checkProductsErpPrice($productInfo)
    {
        $customerErpNumber = $this->getCustomerErpNumber();
        $finalResult = [];

        foreach ($productInfo as $productId => $erpProductNumber) {
            $erpPrice = $this->checkPriceCache($customerErpNumber, $erpProductNumber);

            if ($erpPrice == null) {
                $finalResult[] = ['erp_number' => $erpProductNumber, 'product_ids' => $productId];
            }
        }

        return $finalResult;
    }

    /**
     * @param $customerErpNumber
     * @param $producErpNumber
     *
     * @param $customerErpNumber
     * @param $productErpNumber
     * @return null
     */
    public function checkPriceCache($customerErpNumber, $productErpNumber)
    {
        $erpPrice = null;
        $result = $this->_priceModel->loadByCustomerItem($customerErpNumber, $productErpNumber);
        if(!empty($result)) {
            $savedCustomerPrice = $result['price'];
            $getLastUpdated = $result['last_updated'];
            $now = microtime(true);

            if ($getLastUpdated) {
                $cacheHrs = $this->getCacheHours();
                if ($cacheHrs == '' && !is_int($cacheHrs)) {
                    $cacheHrs = self::DEFAULT_CACHE_HOURS;
                }
                // check if not updated in last n hours
                if ($now < (60 * 60 * $cacheHrs) + strtotime($getLastUpdated)) {
                    if ($savedCustomerPrice != null && $savedCustomerPrice >= 0) {
                        $erpPrice = $savedCustomerPrice;
                    }
                }
            }
        }
        return $erpPrice;
    }

    /**
     * @param null $storeId
     *
     * @param null $storeId
     * @return bool|mixed|string
     */
    public function getCacheHours($storeId = null)
    {
        $helper = $this->getHelper();
        $storeId = ($storeId == 0) ? $storeId : $this->getStoreId();
        return $helper->getDataValue($this->_cacheHours, $storeId);
    }

    /**
     * Updating the Product Erp numbers along with customer Erp number
     *
     * @param int|\LeanSwift\Econnect\Api\Array $productData
     * @param int|string $customerErpNumber
     * @return mixed|void
     */
    public function updateProductCustomerPrice($productData, $customerErpNumber)
    {
        $insertData = null;
        $productErpNumbers = array_keys($productData); //Get erp item no's
        $customerId = $this->getCustomerId();
        $facility = $this->getHelper()->getDataValue($this->_warehousePath, $this->getStoreId());
        $responses = $this->sendPriceRequest($customerErpNumber, $productErpNumbers, $qty = 1, $this->getStoreId());
        if (!empty($responses)) {
            foreach ($responses as $erpItem => $price) {
                $productId = $productData[$erpItem];
                $insertData['itemNo'] = $erpItem;
                $insertData['salesPrice'] = $price;
                $this->_priceModel->updateinTable($insertData, $customerId, $customerErpNumber, $productId, $facility);
            }
        }
    }

    /**
     * Returns Customer Id
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    /**
     * Get the Customer Object
     *
     * @return Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->_customerSession->getCustomer();
    }

    /**
     * Get Customer Session
     *
     * @return Magento\Customer\Model\Session
     */
    public function getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Get the Customer Erp Price
     *
     * @param int $erpCustomerNr
     * @param int $productErpNo
     * @param int $qty
     * @param $storeId
     * @return bool|\LeanSwift\Econnect\Api\arrray|mixed|null
     */
    public function sendPriceRequest($erpCustomerNr, $productErpNo, $qty, $storeId)
    {
        $respData = false;
        $data = null;
        if (is_array($productErpNo)) {
            $productErpNo = array_filter(array_values(array_unique($productErpNo)));
        } else {
            $productErpNo = array($productErpNo);
        }
        if(!empty($productErpNo)) {
            foreach ($productErpNo as $ProductNumber) {
                $postData = $this->_buildRequest($ProductNumber, $erpCustomerNr, $storeId);
                if ($postData) {
                    if ($postData['CustomerNumber'] != null) {
                        $this->_request->setPostValues($postData);
                        $this->_request->send();
                        $response = $this->_request->getResponse();
                        if(!empty($response)) {
                            $respData[$ProductNumber] = $response[$this->_responseField];
                        }
                    }
                }
            }
        }
        return $respData;
    }

    /**
     * @param $productErpNo
     * @param $erpCustomerNr
     * @param $storeId
     *
     * @param $productErpNo
     * @param $erpCustomerNr
     * @param $storeId
     * @return array
     */
    protected function _buildRequest($productErpNo, $erpCustomerNr, $storeId, $qty=1)
    {
        $warehouseValue = $this->getHelper()->getDataValue($this->_warehousePath, $storeId);
        $postData = ['CustomerNumber' => $erpCustomerNr, 'ProductCode' => $productErpNo, 'Quantity' => $qty, 'Warehouse'=> $warehouseValue];
        return $postData;
    }

    /**
     * Get Current Website Id
     *
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->_storeManager->getStore($this->getStoreId())->getWebsite()->getId();
    }

    /**
     * Get Current Store Id
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }

    /**
     * Parsing the response
     *
     * @param $response
     * @return \LeanSwift\Econnect\Model\Econnect\Api\Array
     */
    public function parseResponse($response)
    {
        return $this->_request->_convertResponse($response);
    }


    /**
     * @param      $product
     * @param      $customer
     * @param null $storeId
     *
     * @param $product
     * @param $customer
     * @param null $storeId
     * @return mixed|null
     */
    public function getCustomerPrice($product, $customer, $storeId = null)
    {
        $customerId = $customer->getId();
        $productId = $product->getId();
        $productErpNumber = $product->getErpItemNumber();
        $customerErpNumber = $customer->getErpCustomerNumber();
        if (!$customerErpNumber) {
            return null;
        }
        $websiteId = $customer->getWebsiteId();
        $storeId = ($storeId == 0) ? $storeId : $this->_productHelper->getStoreIdByWebsiteId($websiteId);
        $erpPrice = null;
        $result = $this->_priceModel->loadByCustomerItem($customerErpNumber, $productErpNumber);
        $savedCustomerPrice = $result['price'];
        $getLastUpdated = $result['last_updated'];
        $now = microtime(true);
        if ($getLastUpdated) {
            $cacheHrs = $this->getCacheHours($storeId);
            if ($cacheHrs == '' && !is_int($cacheHrs)) {
                $cacheHrs = self::DEFAULT_CACHE_HOURS;
            }
            // check if not updated in last n hours
            if ($now < (60 * 60 * $cacheHrs) + strtotime($getLastUpdated)) {
                if ($savedCustomerPrice != null && $savedCustomerPrice >= 0) {
                    $erpPrice = $savedCustomerPrice;
                }
            }
        }
        if ($erpPrice == null) {
            $responses = $this->sendPriceRequest($customerErpNumber, array($productErpNumber), $qty = 1, $storeId);
            if ($responses) {
                $facility = $this->getHelper()->getDataValue($this->_warehousePath, $this->getStoreId());
                if ($responses) {
                    foreach ($responses as $erpItem => $price) {
                        $result['itemNo'] = $erpItem;
                        $result['salesPrice'] = $price;
                        $erpPrice = $price;
                        $this->_priceModel->updateinTable($result, $customerId, $customerErpNumber, $productId, $facility);
                    }
                }
            }
        }
        return $erpPrice;
    }
}