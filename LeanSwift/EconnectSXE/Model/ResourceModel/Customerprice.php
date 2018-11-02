<?php

namespace LeanSwift\EconnectSXE\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Customerprice extends AbstractDb
{
    /**
     *
     * Intialize the object
     *
     */
    public function _construct()
    {
        $this->_init('leanswift_econnect_sxe_prices', 'id');
    }

    /**
     *
     * Get the customer price row by the Product Erp item number
     *
     * @param string|int $customerErpNumber
     * @param int $productErpNumber
     * @return Array
     */
    public function loadResourceByCustomerItem($customerErpNumber, $productErpNumber, $customerId = null)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from($this->getMainTable())
            ->where('sxe_customer_nr=:sxe_customer_nr')
            ->where('sxe_productno=:sxe_productno');

        $binds = ['sxe_customer_nr' => $customerErpNumber, 'sxe_productno' => $productErpNumber];
        return $adapter->fetchRow($select, $binds);
    }


    /**
     * @param $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateRecord($data)
    {
        $adapter = $this->getConnection();
        $adapter->insertOnDuplicate($this->getMainTable(), $data);
    }

    /**
     * @param $customerErpNumber
     * @param $productErpNumber
     * @param null $customerId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteRecord($customerErpNumber, $productErpNumber, $customerId = null)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $table = $this->getMainTable();
            $where = ['sxe_customer_nr = ?' => $customerErpNumber, 'sxe_productno = ?' => $productErpNumber];
            $connection->delete($table, $where);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
    }
}