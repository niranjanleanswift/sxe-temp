<?php
/**
 * *
 *  * LeanSwift Extension
 *  *
 *  * NOTICE OF LICENSE
 *  *
 *  * This source file is subject to the LeanSwift Connector Extension License
 *  * that is bundled with this package in the file LICENSE.txt located in the Connector Server.
 *  *
 *  * DISCLAIMER
 *  *
 *  * This extension is licensed and distributed by LeanSwift. Do not edit or add to this file
 *  * if you wish to upgrade Extension and Connector to newer versions in the future.
 *  * If you wish to customize Extension for your needs please contact LeanSwift for more
 *  * information. You may not reverse engineer, decompile,
 *  * or disassemble LeanSwift Connector Extension (All Versions), except and only to the extent that
 *  * such activity is expressly permitted by applicable law not withstanding this limitation.
 *  *
 *  * @copyright   Copyright (C) Leanswift Solutions, Inc - All Rights Reserved
 *  * Unauthorized copying of this file, via any medium is strictly prohibited.
 *  * Proprietary and confidential.
 *  * Terms and conditions http://leanswift.com/leanswift-eula/
 *  * @category Norsea
 *
 */

namespace LeanSwift\EconnectSXE\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Serialize\SerializerInterface;

class Configuration extends AbstractDb
{
    protected $_serializeInterface;
    public function __construct(
        Context $context,
        SerializerInterface $serializerInterface,
        $connectionName = null
    )
    {
        $this->_serializeInterface = $serializerInterface;
        parent::__construct($context, $connectionName);
    }

    public function insertConfiguration($list)
    {
        if (!empty($list)) {
            try {
                $list['response'] = $this->_serializeInterface->serialize($list['response']);
                $connection = $this->getConnection();
                $connection->beginTransaction();
                $this->getConnection()->insertOnDuplicate(
                    $this->getMainTable(),
                    $list
                );
                $connection->commit();
            } catch (LocalizedException $e) {
                $connection->rollBack();
            }
        }
        return $this;
    }

    public function loadByterm($term) {
        $list = $this->getList($term);
        return $this->_serializeInterface->unserialize(current($list));
    }

    public function getList($term)
    {
        $connection = $this->getConnection();
        try {
            $select = $connection->select()->from(
                ['m' => $this->getMainTable()],
                ['response']
            );
            $select->where('term = ?', $term);
        } catch (LocalizedException $e) {
        }
        return $connection->fetchCol($select);
    }

    protected function _construct()
    {
        $this->_init('leanswift_econnect_sxe_configuration', 'id');
    }

}
