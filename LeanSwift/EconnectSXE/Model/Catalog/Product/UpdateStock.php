<?php

namespace LeanSwift\EconnectSXE\Model\Catalog\Product;

use LeanSwift\EconnectSXE\Helper\Configurations;
use LeanSwift\EconnectSXE\Helper\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use LeanSwift\EconnectSXE\Api\UpdateStock as UpdateStockInterface;
use LeanSwift\EconnectSXE\Model\Soap\ProductStock;

class UpdateStock implements UpdateStockInterface
{
    const RESPONSE_QTY_PARAMETER = 'NetAvailable';

    protected $_stockRegistryInterface;
    protected $_stockItemInterface;
    protected $_productRepository;
    protected $_updateStock;
    protected $_scopeConfig;
    protected $_path;
    protected $_productStock;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StockRegistryInterface $StockRegistryInterface,
        StockItemInterface $StockItemInterface,
        $backendEnablePath = '',
        ProductStock $ProductStock
    ) {
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_stockItemInterface = $StockItemInterface;
        $this->_stockRegistryInterface = $StockRegistryInterface;
        $this->_path = $backendEnablePath;
        $this->_productStock = $ProductStock;
    }

    public function setEnablePath($path)
    {
        $this->_path = $path;
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

    public function getCurrentWarehouse()
    {
        return $this->_scopeConfig->getValue(
            Configurations::XML_DEFAULT_WAREHOUSE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function update($productId) {
        if($this->canUpdateStock()){
            $product = $this->getProduct($productId);
            /** Form the Request values */
            $postValues['Product'] = Product::getSXEProductNumber($product);
            $postValues['UseCrossReferenceFlag'] = 0;
            $postValues['Whse'] = $this->getCurrentWarehouse();
            $this->_productStock->setPostValues($postValues);
            //** Send the request */
            $this->_productStock->send();
            /** @var Retreive the response for the request $response */
            $response = $this->_productStock->getResponse();
            if(!empty($response)) {
                $this->updateStock($productId, $product->getSku(), $response[self::RESPONSE_QTY_PARAMETER]);
            }
        }
        return $this;
    }

    public function updateStock($productId, $sku, $qty)
    {
        $StockRegistryInterface = $this->_stockRegistryInterface;
        $stockItemInterface = $this->_stockItemInterface;
        $stockItemInterface
                ->setProductId($productId)
                ->setQty($qty);
        $StockRegistryInterface->updateStockItemBySku($sku, $stockItemInterface);
    }
}