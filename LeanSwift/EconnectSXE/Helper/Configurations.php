<?php
namespace LeanSwift\EconnectSXE\Helper;

class Configurations
{
    /** Default store configuration */
    const XML_PATH_CONNECTION_STRING = 'econnectSXE/general_config/connection_string';
    const XML_PATH_COMPANY_NUMBER = 'econnectSXE/basic_data/company';
    const XML_PATH_OPERATOR_INTIALS = 'econnectSXE/basic_data/operator';
    const XML_PATH_OPERATOR_PASSWORD = 'econnectSXE/basic_data/operator_password';
    const XML_SOAP_SERVICE_URL = 'econnectSXE/general_config/location_url';

    const XML_DEFAULT_WAREHOUSE = 'econnectSXE/basic_data/warehouse';
    const XML_DEFAULT_DEBUG_ENABLE = 'econnectSXE/general_config/debug_log_data';

    /** Stock Settings */
    const XML_STOCK_ENABLE_PRODUCT_VIEW = 'econnectSXE/stock/enable_productview';
    const XML_STOCK_ENABLE_ADD_TO_CART = 'econnectSXE/stock/enable_cart';
    const XML_STOCK_ENABLE_CHECKOUT_PAGE = 'econnectSXE/stock/enable_checkout';
    const XML_STOCK_ENABLE_LOGGER = 'econnectSXE/stock/log';

    /** Customer Price settings */

    const XML_CUSTOMER_PRICE_ENABLE_PRODUCT_PRICE = 'econnectSXE/customer_price/enable_price';
    const XML_CUSTOMER_PRICE_ENABLE_PRODUCT_LISTING = 'econnectSXE/customer_price/enable_listing';
    const XML_CUSTOMER_PRICE_CACHE_HOURS = 'econnectSXE/customer_price/cache_hours';
    const XML_CUSTOMER_PRICE_LOGGER = 'econnectSXE/customer_price/log';
}
