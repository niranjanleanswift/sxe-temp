<?php
namespace LeanSwift\EconnectSXE\Observer\Stock;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use LeanSwift\EconnectSXE\Api\StockInterface;
use LeanSwift\EconnectSXE\Helper\Data;

class StockSyncOnAddToCartObserver implements ObserverInterface
{
    protected $_request;
    protected $_productStock;
    protected $_helperData = null;

    public function __construct(
        RequestInterface $RequestInterface,
        StockInterface $updateStock,
        Data $data
    )
    {
        $this->_request = $RequestInterface;
        $this->_productStock = $updateStock;
        $this->_helperData = $data;
    }

    public function execute(Observer $observer)
    {
        $path = $this->_productStock->getEnablePath();
        $storeId = $this->_helperData->getStoreId();
        $isSyncEnabled = $this->_helperData->getDataValue($path, $storeId);
        if ($isSyncEnabled) {
            $requestParams = $this->_request->getParams();

            if (isset($requestParams['selected_configurable_option']) && $requestParams['selected_configurable_option'] != null) {
                $productId = (int)$requestParams['selected_configurable_option']; //Get associated product from configurable product
                $this->_productStock->updateProductStock($productId); //Update product stock based on M3 response
            } else if (isset($requestParams['super_group']) && $requestParams['super_group'] != null) {
                //Get associated product Ids whose qty's greater than zero when add to cart
                $productIds = array_filter($requestParams['super_group'], function ($qty) {
                    return $qty > 0;
                });
                if (count($productIds)) {
                    $this->_productStock->prepareGroupedProductStock($productIds);
                }
            } else {
                $productId = (int)$requestParams['product'];
                $this->_productStock->updateProductStock($productId); //Update product stock based on M3 response
            }
        }
    }
}