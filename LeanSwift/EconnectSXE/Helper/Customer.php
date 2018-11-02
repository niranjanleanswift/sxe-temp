<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LeanSwift\EconnectSXE\Helper;

class Customer
{
    const SXE_CUSTOMER_NUMBER = 'sxe_customer_nr';

    public static function getCustomerNumber($customer) {
        return $customer->getData(self::SXE_CUSTOMER_NUMBER);
    }
}
