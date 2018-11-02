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
 * @copyright   Copyright (c) 2015 LeanSwift Inc. (http://www.leanswift.com)
 * @license     http://www.leanswift.com/license/connector-extension
 */

namespace LeanSwift\EconnectSXE\Observer\Stock;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use LeanSwift\EconnectSXE\Api\StockInterface;
use LeanSwift\EconnectSXE\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class StockSyncOnProductViewObserver implements ObserverInterface
{
    protected $_productStock;
    protected $_request;
    protected $_helperData;
    protected $_productRepository;

    public function __construct(
        StockInterface $updateStock,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        Data $data
    )
    {
        $this->_request = $request;
        $this->_productStock = $updateStock;
        $this->_helperData = $data;
        $this->_productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        $path = $this->_productStock->getEnablePath();
        $storeId = $this->_helperData->getStoreId();
        $isSyncEnabled = $this->_helperData->getDataValue($path, $storeId);
        if ($isSyncEnabled) {
            $product = null;
            $params = $this->_request->getParams(); //get product id from params
            $productId = $params['id'];
            try {
                $product = $this->_productRepository->getById($productId, false, $storeId); //Get product by Id from repository
            } catch (NoSuchEntityException $e) {
                return false;
            }
            if ($product) {
                $logger = $this->_productStock->getLogger();
                $canWriteLog = $this->_productStock->canWriteLog();
                $typeId = $product->getTypeId();
                switch ($typeId) {
                    case Configurable::TYPE_CODE:
                        if ($canWriteLog) {
                            $logger->info('Initialize stock update for configurable products..');
                        }
                        $this->_productStock->updateConfigurableStock($product);
                        break;
                    case Grouped::TYPE_CODE:
                        if ($canWriteLog) {
                            $logger->info('Initialize stock update for grouped products..');
                        }
                        $this->_productStock->updateConfigurableStock($product, 'grouped');
                        break;
                    default:
                        if ($canWriteLog) {
                            $logger->info('Initialize stock update for simple products..');
                        }
                        $this->_productStock->updateProductStock($product);
                }
                return $this;
            }
        }
    }
}