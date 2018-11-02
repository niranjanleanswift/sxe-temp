<?php

namespace LeanSwift\EconnectSXE\Model\Catalog\Product;

use LeanSwift\EconnectSXE\Helper\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use LeanSwift\EconnectSXE\Api\StockInterface;
use LeanSwift\EconnectSXE\Model\Soap\AbstractRequest;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\App\ResourceConnection as DbConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Checkout\Model\Cart as CartItem;

class Stock implements StockInterface
{

    protected $_stockRegistryInterface;
    protected $_productRepository;
    protected $_updateStock;
    protected $_scopeConfig;
    protected $_path;
    protected $_productStock;
    protected $_responseField;
    protected $_warehousepath;
    protected $__productHelper;
    protected $_stockIndexerProcessor;
    protected $_connection = null;
    protected $_reindexFlag = false;
    protected $_indexerRegistry;
    protected $_canWriteLog = false;
    protected $_logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StockRegistryInterface $StockRegistryInterface,
        $backendEnablePath = '',
        $responseField = '',
        $warehousePath = '',
        AbstractRequest $ProductStock,
        StockHelper $stock,
        ProductFactory $productFactory,
        Product $productHelper,
        Processor $stockIndexerProcessor,
        DbConnection $dbConnection,
        IndexerRegistry $indexerRegistry,
        CartItem $cart
    ) {
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_stockRegistryInterface = $StockRegistryInterface;
        $this->_path = $backendEnablePath;
        $this->_productStock = $ProductStock;
        $this->_warehousepath = $warehousePath;
        $this->_responseField = $responseField;
        $this->_stockHelper = $stock;
        $this->_productFactory = $productFactory;
        $this->_productHelper = $productHelper;
        $this->_stockIndexerProcessor = $stockIndexerProcessor;
        $this->_connection = $dbConnection->getConnection('write');
        $this->_indexerRegistry = $indexerRegistry;
        $this->_cartItem = $cart;
        $this->_canWriteLog =  $this->logEnabled();
        $this->_logger = $this->_productStock->getLogger();
    }

    public function setEnablePath($path)
    {
        $this->_path = $path;
    }

    public function getLogger() {
        return $this->_logger;
    }

    public function getEnablePath() {
        return $this->_path;
    }

    public function logEnabled() {
        return $this->_scopeConfig->getValue(
            $this->_productStock->getLoggerEnablePath(),
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function canWriteLog() {
        return $this->_canWriteLog;
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getProduct($productId) {
        try{
            return $this->_productRepository->getById($productId);
        }
        catch (NoSuchEntityException $e) {
            return false;
        }
    }

    public function getCurrentWarehouse()
    {
        return $this->_scopeConfig->getValue(
            $this->_warehousepath,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function updateProductStock($productId) {
        if ($productId != null && !$productId instanceof \Magento\Catalog\Model\Product) {
            $product =  $this->_productFactory->create()->load($productId);
        }
        else {
            $product = $productId;
        }
        if (!$product) {
            return false;
        }
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            //Prepare associated products of Config products
            $response = $this->_prepareAssociatedProducts($product);
            return $response;
        }
        if ($product->getTypeId() == Grouped::TYPE_CODE) {
            //Prepare associated products of grouped products
            $response = $this->_prepareAssociatedProducts($product, 'grouped');
            return $response;
        }
        $sxeProductNumber = Product::getSXEProductNumber($product);
        if($sxeProductNumber) {
            $response = $this->_createRequest([$sxeProductNumber]);
            if(!empty($response)) {
                $output = $response[$sxeProductNumber];
                $this->updateStock($product, $output[$this->_responseField]);
            }
        }
        return $this;
    }

    public function prepareGroupedProductStock($productIds)
    {
        $stockData = null;
        $erpItemArray = null;
        $productIds = array_keys($productIds); //Remove qty from requested product Ids in order to filter data from below product collection
        $products = $this->_productFactory->create()->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('in' => $productIds));
        $productNumberArray = array_map('trim', $products->getColumnValues(Product::SXE_PRODUCT_NUMBER));
        if (count($erpItemArray)) {
            $stockData = $this->_createRequest([$productNumberArray]);
            if ($stockData) {
                $this->_reindexFlag = false;
                foreach ($products as $product) {
                    $sxeProductNumber = trim($product->getData(Product::SXE_PRODUCT_NUMBER));
                    $response = $stockData[$sxeProductNumber];
                    if ($response) {
                        $this->updateStock($product, $response[$this->_responseField]);
                    }
                }
                if ($this->_reindexFlag) {
                    $indexer = $this->_indexerRegistry->get('cataloginventory_stock');
                    $indexer->reindexAll();
                }
            }
        }
    }

    public function updateConfigurableStock($product = null, $flag = null)
    {
        $response = null;
        if (!$product) {
            $products = $this->_product->create()->getCollection()
                ->addAttributeToSelect(Product::SXE_PRODUCT_NUMBER)
                ->addAttributeToFilter('type_id', Configurable::TYPE_CODE);

            foreach ($products as $_product) {
                //Prepare associated products of All configurable products
                $response = $this->_prepareAssociatedProducts($_product);
            }
        } else {
            //Prepare associated products of browsed configurable products
            $response = $this->_prepareAssociatedProducts($product, $flag);
        }

        return $response;
    }

    protected function _prepareAssociatedProducts($product, $flag = null)
    {
        $message = null;
        $productCount = null;
        $associatedProducts = null;

        $_product = $product;
        $storeId = $this->getStoreId();
        $typeId = $_product->getTypeId();

        if ($flag == 'grouped') {
            $associatedProductIds = $_product->getTypeInstance()->getChildrenIds($_product->getId());
            $associatedProducts = $this->getAssociatedProductsCollection($associatedProductIds);
        } else {
            if (Product::getSXEProductNumber($_product)) {
                $associatedProducts = $_product->getTypeInstance()->getUsedProductCollection($_product)
                    ->setFlag('has_stock_status_filter', false)
                    ->addStoreFilter($storeId)
                    ->addAttributeToSelect(Product::SXE_PRODUCT_NUMBER);
            }
        }
        if ($associatedProducts) {
            $productCount += count($associatedProducts);
            $updateCounter = $this->_saveStock($associatedProducts);
            if ($updateCounter) {
                $this->_stockHelper->assignStatusToProduct($product, true);
                $this->_stockIndexerProcessor->reindexAll();
            }
            $message = 'Products: ' . $productCount . '; Updates: ' . $updateCounter;
            $logger = $this->getLogger();
            //Print log message  for configurable products
            if ($typeId == Configurable::TYPE_CODE) {
                if($this->canWriteLog())
                {
                    $logger->info('Updating configurable type product stock: ' . $message);
                }
            }

            //Print log message for groped products
            if ($typeId == Grouped::TYPE_CODE) {
                if($this->canWriteLog())
                {
                    $logger->info('Updated grouped type product stock: ' . $message);
                }
            }
        }
        return $message;
    }

    public function getAssociatedProductsCollection($associatedProductIds)
    {
        $productIds = reset($associatedProductIds);
        $product = null;
        if (count($productIds)) {
            $product = $this->_productFactory->create()->getCollection()
                ->setFlag('has_stock_status_filter', false)
                ->addAttributeToSelect('*')
                ->addStoreFilter($this->getStoreId())
                ->addAttributeToFilter('entity_id', array('in' => $productIds));
        }
        return $product;
    }

    protected function _saveStock($associatedProducts)
    {
        $updateCounter = 0;
        $flag = false;
        $productNumberArray = null;
        $productNumberArray = $associatedProducts->getColumnValues(Product::SXE_PRODUCT_NUMBER);
        $trimmedArray = array_map('trim', $productNumberArray);
        if (count($trimmedArray)) {
            $stockData = $this->_createRequest($trimmedArray);
            if ($stockData) {
                $stockInfo = [];
                foreach ($associatedProducts as $product) {
                    $productNumber = trim($product->getData(Product::SXE_PRODUCT_NUMBER));
                    if ($productNumber) {
                        if (array_key_exists($productNumber, $stockData)) {
                            $flag = true;
                            $stockQty = $stockData[$productNumber];
                            $sku = $product->getSku();
                            $entityId = $product->getId();
                            $stockInfo[$sku] = array('product_id' => $entityId,
                                'qty' => $stockQty, 'stock_id' => 1, 'is_in_stock' => 0
                            );
                            if ($stockQty > 0) { //Check if stockQty value is greater than zero, somecases M3 response may return negative values, for that cases product should be out of stock
                                $stockInfo[$sku]['is_in_stock'] = 1;
                            }
                        }
                    }
                }

                if ($flag) {
                    $updateCounter = $this->_directUpdate($stockInfo, false);
                }
            }
        }

        return $updateCounter;
    }

    public function prepareCartItemSync()
    {
        $quote = $this->_cartItem->getQuote();
        $cartItems = $this->_cartItem->getItems();
        $errorFlag = false;
        $stockInfo = [];
        if ($quote->hasItems()) {
            $sxeItemArray = [];
            $entityIdArray = [];
            $skuArray = [];
            $originalQtyArray = [];
            foreach ($cartItems as $quoteItems) {
                $product = $quoteItems->getProduct();
                if ($product->getTypeId() == Configurable::TYPE_CODE) {
                    continue;
                }

                if ($product->getTypeId() == Grouped::TYPE_CODE) {
                    continue;
                }
                $sxeProductNumber = trim(Product::getSXEProductNumber($product));
                //if product not exist from quote product collection simply load
                if(!$sxeProductNumber) {
                    $productModel =  $this->_productFactory->create()->load($product->getId());
                    $sxeProductNumber = trim(Product::getSXEProductNumber($productModel));
                }
                if ($sxeProductNumber) {
                    $sxeItemArray[] = $sxeProductNumber;
                    $entityIdArray[$sxeProductNumber] = $product->getId();
                    $skuArray[$sxeProductNumber] = $product->getSku();
                    $originalQtyArray[$sxeProductNumber] = $product->getQty();
                }
            }
            if (count($sxeItemArray)) {
                $stockData = $this->_createRequest($sxeItemArray);
                if ($stockData) {
                    foreach ($stockData as $itemNo => $itemQty) {
                        $sku = $skuArray[$itemNo];
                        $productId = $entityIdArray[$itemNo];
                        $existingQty = $originalQtyArray[$itemNo];
                        $newQty = $itemQty[$this->_responseField];
                        if ($newQty != $existingQty) {
                            $stockInfo[$sku] = array('product_id' => $productId, 'qty' => $newQty, 'stock_id' => 1);
                            if ($newQty > 0) {//Check if stockQty value is greater than zero, M3 response may return negative values, for that cases product should be out of stock
                                $stockInfo[$sku]['is_in_stock'] = 1;
                            } else {
                                $stockInfo[$sku]['is_in_stock'] = 0;
                                $errorFlag = true; //set error flag, redirect to shopping cart on checkout
                            }
                        }
                    }
                    if($this->canWriteLog())
                    {
                        $logger = $this->getLogger();
                        $logger->info(print_r($stockInfo,true));
                    }
                    if (count($stockInfo)) {
                        $this->_directUpdate($stockInfo, false);
                    }
                    if ($errorFlag) {
                        $this->_cartItem->getQuote()->addErrorInfo('stock', 'cataloginventory', null, __('Some of the products are out of stock.'));
                    }
                }
            }

        } else {
            if($this->canWriteLog()) {
                $logger = $this->getLogger();
                $logger->info('No Products In Cart');
            }
        }
    }

    protected function _directUpdate($stockData, $batch = false)
    {
        $whereEntiryId = null;
        $message = '';
        $beforeTime = microtime(true);

        // get stock table to update product qty
        $stockTable = $this->_connection->getTableName('cataloginventory_stock_item');

        // Insert rows
        if (!empty($stockData)) {
            $this->_connection->insertOnDuplicate($stockTable, $stockData, ['qty', 'is_in_stock']);
        }
        //Reindex catalog inventory stock
        $this->_stockIndexerProcessor->reindexAll();

        $afterTime = microtime(true);
        $time = $afterTime - $beforeTime;
        $totalCount = count($stockData);
        if ($batch) {
            $message = ", Batch $batch";
        }

        if($this->canWriteLog())
        {
            $logger = $this->getLogger();
            $logger->info("Updated records:  : $totalCount $message   Time taken: $time");
        }
        return $totalCount;
    }

    protected function _createRequest($productNumberList) {
        foreach ($productNumberList as $productNumber) {
            $result = null;
            /** Form the Request values */
            $postValues['Product'] = $productNumber;
            $postValues['UseCrossReferenceFlag'] = 0;
            $postValues['Whse'] = $this->getCurrentWarehouse();
            $this->_productStock->setPostValues($postValues);
            //** Send the request */
            $this->_productStock->send();
            /** @var Retreive the response for the request $response */
            $response[$productNumber] = $this->_productStock->getResponse();
        }
        return $response;
    }


    /**
     * @param $productId
     * @param $qty
     * @param null $websiteId
     *
     * Update stock based on the product ID and Qty
     */
    public function updateStock($product, $newQty, $websiteId=null)
    {
        $saleableStatus = false;
        $StockRegistryInterface = $this->_stockRegistryInterface;
        $stockItem = $StockRegistryInterface->getStockItem($product->getId(), $websiteId);
        $originalQty = $stockItem->getQty();
        if (($newQty != $originalQty)|| ($stockItem->getBackorders() != 0 )) { //Check if both the existing qty and new qty are same
            $this->_reindexFlag = true;
            if (($newQty > 0)|| ($stockItem->getBackorders() != 0 )) { //Check if stockQty value is greater than zero, M3 response may return negative values, for that cases product should be out of stock
                $saleableStatus = true;
            }
            $stockItem->setQty($newQty);
            $stockItem->setIsInStock($saleableStatus);
            /* Here repository stock update is handled instead of direct query in order to reflect the correct stock status when add to cart */
            $this->_stockRegistryInterface->updateStockItemBySku($product->getSku(), $stockItem);
            $this->_stockHelper->assignStatusToProduct($product, $saleableStatus);//To change the product status in product view page
        }
    }
}