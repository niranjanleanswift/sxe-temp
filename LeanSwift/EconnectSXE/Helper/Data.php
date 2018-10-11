<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LeanSwift\EconnectSXE\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /** Common namespace */
    const TEMP_NAMESPACE = 'http://tempuri.org/';
    const PROXY_NAMESPACE = 'http://schemas.datacontract.org/2004/07/ProxyGen.com.infor.sxapi.connection';
    const CONNECTION_STRING = 'callConnection';

    /** Default store configuration */
    const XML_PATH_CONNECTION_STRING = 'econnectSXE/general_config/connection_string';
    const XML_PATH_COMPANY_NUMBER = 'econnectSXE/basic_data/company';
    const XML_PATH_OPERATOR_INTIALS = 'econnectSXE/basic_data/operator';
    const XML_PATH_OPERATOR_PASSWORD = 'econnectSXE/basic_data/operator_password';
    const XML_SOAP_SERVICE_URL = 'econnectSXE/general_config/location_url';

    /** Product Paramaters */
    const PRODUCT_NAMESPACE = 'http://schemas.datacontract.org/2004/07/ProxyGen.com.infor.sxapi.ICGetWhseProductDataQuantity';
    const PRODUCT_API= 'ICGetWhseProductDataQuantity';


    /** Warehouse Paramaters */
    const WAREHOUSE_NAMESPACE = 'http://schemas.datacontract.org/2004/07/ProxyGen.com.infor.sxapi.ICGetWarehouseList';
    const WAREHOUSE_API= 'ICGetWarehouseList';

    /** Other constants */
    const SESSION_MODEL = '0';

    /** Log informations */
    const WAREHOUSE_LOG = '/var/log/sxe/warehouse.log';
    const STOCK_SYNC_LOG = '/var/log/sxe/stockSynch.log';
    const PRODUCT_SYNC_LOG = '/var/log/sxe/productSynch.log';
    const CUSTOMER_SYNC_LOG = '/var/log/sxe/customerSynch.log';

    const SEPARATOR = ':';

    public function getDataValue($path) {
        return $this->scopeConfig->getValue(
            $path
        );
    }
}
