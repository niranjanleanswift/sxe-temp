<?php

namespace LeanSwift\EconnectSXE\Model\Catalog\Product;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class UpdateStock implements
{
    protected $_stockRegistryInterface;
    protected $_stockItemInterface;
    protected $_productRepository;
    protected $_updateStock;
    protected $_scopeConfig;
    protected $_path;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StockRegistryInterface $StockRegistryInterface,
        StockItemInterface $StockItemInterface,
        $backendEnablePath = ''
    ) {
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_stockItemInterface = $StockItemInterface;
        $this->_stockRegistryInterface = $StockRegistryInterface;
        $this->_path = $backendEnablePath;
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getProduct($productId) {
        return $this->_productRepository->getById($productId);
    }

    public function canUpdateStock() {
        return $this->_scopeConfig->isSetFlag(
            $this->_path,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function update($productId) {
        if($this->canUpdateStock()){
            $product = $this->getProduct($productId);
            $this->updateStock($productId, $product->getSku());
        }
        return $this;
    }


    public function updateStock($productId, $sku)
    {
        $StockRegistryInterface = $this->_stockRegistryInterface;
        $stockItemInterface = $this->_stockItemInterface;
        $stockItemInterface
                ->setProductId($productId)
                ->setQty(10);
        $StockRegistryInterface->updateStockItemBySku($sku, $stockItemInterface);
    }
}