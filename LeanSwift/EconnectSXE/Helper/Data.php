<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LeanSwift\EconnectSXE\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\App\Helper\Context;
class Data extends AbstractHelper
{
    /** Common namespace */
    const TEMP_NAMESPACE = 'http://tempuri.org/';
    const PROXY_NAMESPACE = 'http://schemas.datacontract.org/2004/07/ProxyGen.com.infor.sxapi.connection';
    const CONNECTION_STRING = 'callConnection';

    const ISERVICE_API = 'IService/';

    /** Product Paramaters */
    const PRODUCT_NAMESPACE = 'http://schemas.datacontract.org/2004/07/ProxyGen.com.infor.sxapi.ICGetWhseProductDataQuantity';
    const PRODUCT_API= 'ICGetWhseProductDataQuantity';


    /** Warehouse Paramaters */
    const WAREHOUSE_NAMESPACE = 'http://schemas.datacontract.org/2004/07/ProxyGen.com.infor.sxapi.ICGetWarehouseList';
    const WAREHOUSE_API= 'ICGetWarehouseList';

    /** Customer price Parameters */
    const CUSTOMER_PRICE_NAMESPACE = 'http://schemas.datacontract.org/2004/07/ProxyGen.com.infor.sxapi.OEPricingV4';
    const CUSTOMER_PRICE_API= 'OEPricingV4';

    /** Other constants */
    const SESSION_MODEL = '0';
    const SEPARATOR = ':';

    protected $_storeManager = null;

    public function __construct(
        StoreManager $storeManager,
        Context $context
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getDataValue($path, $store=null) {
        if(!$store) {
            $store = $this->getStoreId();
        }
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getStoreId()
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        return $storeId;
    }
}
