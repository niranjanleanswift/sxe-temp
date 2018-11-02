<?php

namespace LeanSwift\EconnectSXE\Api;

interface StockInterface
{
    public function setEnablePath($path);
    public function getEnablePath();
    public function updateProductStock($productId);
    public function updateConfigurableStock($product);
    public function prepareGroupedProductStock($productIds);
    public function prepareCartItemSync();
    public function getLogger();
    public function canWriteLog();
}