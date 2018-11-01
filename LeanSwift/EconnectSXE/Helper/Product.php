<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LeanSwift\EconnectSXE\Helper;

use Magento\Framework\Api\AttributeValue;
use Magento\Catalog\Model\ProductFactory;

class Product
{
    const SXE_PRODUCT_NUMBER = 'sxe_productno';

    public static function getSXEProductNumber($product) {
        $attributeValue = $product->getCustomAttribute(self::SXE_PRODUCT_NUMBER);
        if($attributeValue instanceof AttributeValue) {
            return $attributeValue->getValue();
        }
        return '';
    }
}
