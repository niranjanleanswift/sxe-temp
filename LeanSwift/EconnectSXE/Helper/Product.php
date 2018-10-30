<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LeanSwift\EconnectSXE\Helper;

use Magento\Framework\Api\AttributeValue;

class Product
{
    public static function getSXEProductNumber($product) {
        $attributeValue = $product->getCustomAttribute('sxe_productno');
        if($attributeValue instanceof AttributeValue) {
            return $attributeValue->getValue();
        }
        return '';
    }
}
