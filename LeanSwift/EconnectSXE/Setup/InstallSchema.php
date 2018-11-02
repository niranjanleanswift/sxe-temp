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

namespace LeanSwift\EconnectSXE\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /*
         * Create table 'leanswift_econnect_sxe_configuration'
         */
        $configurationTable = $installer->getTable('leanswift_econnect_sxe_configuration');
        $table = $installer->getConnection()
            ->newTable(
                $configurationTable
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'term',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Transaction Term'
            )
            ->addColumn(
                'response',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Response'
            )
            ->addIndex(
                $installer->getIdxName(
                    $configurationTable,
                    ['term'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                'term',
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment(
                'LeanSwift EconnectSXE Configuration Table'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'leanswift_econnect_sxe_prices'
         */
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('leanswift_econnect_sxe_prices')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'sxe_productno',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'SXe Product Number'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Product Id'
            )
            ->addColumn(
                'sxe_customer_nr',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'SXe Customer Number'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Customer Id'
            )
            ->addColumn(
                'warehouse',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Warehouse'
            )
            ->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Qty'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '15,2',
                [],
                'Price'
            )
            ->addColumn(
                'last_updated',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Last Updated'
            )
            ->addIndex(
                $installer->getIdxName('leanswift_econnect_sxe_prices', ['id']),
                ['id']
            )->addIndex(
                $installer->getIdxName('leanswift_econnect_sxe_prices', ['sxe_productno']),
                ['sxe_productno']
            )->addIndex(
                $installer->getIdxName('leanswift_econnect_sxe_prices', ['customer_id']),
                ['customer_id']
            )->addIndex(
                $installer->getIdxName('leanswift_econnect_sxe_prices', ['product_id']),
                ['product_id']
            )
            ->setComment(
                'LeanSwift Econnect Sxe Prices Table'
            );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}