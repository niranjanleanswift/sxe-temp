<?php

namespace LeanSwift\EconnectSXE\Api;

interface UpdateStock
{
    public function setEnablePath($path);
    public function updateProductStock($productId);
    public function prepareGroupedProductStock($productIds);
    public function prepareCartItemSync();
}