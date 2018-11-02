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

namespace LeanSwift\Econnect\Observer\CustomerPrice;

use LeanSwift\Econnect\Helper\Data;
use LeanSwift\Econnect\Api\PriceInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class BindCustomerPriceonCartPageObserver
 * @package LeanSwift\Econnect\Observer\CustomerPrice
 */
class BindCustomerPriceonCartPageObserver implements ObserverInterface
{
    /**
     * Econnect helper
     *
     * @var LeanSwift\Econnect\Helper\Data
     */
    protected $_helperData = null;

    /**
     * Customer price interface
     *
     * @var PriceInterface
     *
     */
    protected $_priceInterface;

    /**
     * BindCustomerPriceonCartPageObserver constructor.
     * @param Data $helperData
     * @param PriceInterface $priceInterface
     */
    public function __construct(Data $helperData, Priceinterface $priceInterface)
    {
        $this->_helperData = $helperData;
        $this->_priceInterface = $priceInterface;
    }

    /**
     * Binds the customer price on loading the cart page
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }
}