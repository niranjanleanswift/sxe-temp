<?php
namespace LeanSwift\EconnectSXE\Observer\Stock;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use LeanSwift\EconnectSXE\Api\UpdateStock;

class StockSyncOnAddToCartObserver implements ObserverInterface
{
    protected $_request;
    protected $_updateStock;

    public function __construct(
        RequestInterface $RequestInterface,
        UpdateStock $updateStock
    )
    {
        $this->_request = $RequestInterface;
        $this->_updateStock = $updateStock;
    }

    public function execute(Observer $observer)
    {
        $productId = (int)$this->_request->getParam('product');
        $this->_updateStock->update($productId);
    }
}