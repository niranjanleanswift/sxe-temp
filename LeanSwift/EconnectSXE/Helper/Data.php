<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LeanSwift\EconnectSXE\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

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

    /** Other constants */
    const SESSION_MODEL = '0';
    const SEPARATOR = ':';

    public function getDataValue($path, $store=null) {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
