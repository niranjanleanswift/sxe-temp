<?php

namespace LeanSwift\EconnectSXE\Observer\CustomerPrice;

use LeanSwift\EconnectSXE\Helper\Data;
use LeanSwift\EconnectSXE\Api\PriceInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
/**
 * Class BindCustomerPriceonViewPageLoadObserver
 * @package LeanSwift\Econnect\Observer\CustomerPrice
 */
class BindCustomerPriceonViewPageLoadObserver implements ObserverInterface
{
    protected $_helperData = null;

    /**
     * Customer price interface
     *
     * @var PriceInterface
     */
    protected $_priceInterface;

    protected $_registry;

    /**
     * BindCustomerPriceonViewPageLoadObserver constructor.
     * @param Data $helperData
     * @param PriceInterface $priceInterface
     */
    public function __construct(
        Data $helperData,
        Priceinterface $priceInterface,
        Registry $registry
    ){
        $this->_helperData = $helperData;
        $this->_priceInterface = $priceInterface;
        $this->_registry = $registry;
    }

    /**
     * Binds the customer price on loading the view page
     *
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        try {
            $path = $this->_priceInterface->getEnablePath();
            $storeId = $this->_helperData->getStoreId();
            $isSyncEnabled = $this->_helperData->getDataValue($path, $storeId);
            $this->_registry->register('enabled_customer_price', $isSyncEnabled);
            if ($isSyncEnabled) {
                $erpCustomerNr = $this->_priceInterface->getCustomerErpNumber();
                if ($erpCustomerNr) {
                    $product = $this->_priceInterface->getCurrentProduct();
                    $this->_priceInterface->updateCustomerPriceForProduct($product);
                }
            }
        } catch (\Exception $e) {
            $logger = $this->_priceInterface->getLogger();
            $canWriteLog = $this->_priceInterface->canWriteLog();
            if ($canWriteLog) {
                $logger->info($e->getMessage());
            }
        }

        return $this;
    }
}