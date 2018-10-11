<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LeanSwift\EconnectSXE\Model\Config\Source;

class Version implements \Magento\Framework\Option\ArrayInterface
{
    const VERSION_6_10 = 0;
    const VERSION_11 = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => self::VERSION_6_10, 'label' => __('6-10')], ['value' => self::VERSION_11, 'label' => __('11')]];
    }
}
