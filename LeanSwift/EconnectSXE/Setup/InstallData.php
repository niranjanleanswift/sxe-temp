<?php

namespace LeanSwift\EconnectSXE\Setup;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
class InstallData implements InstallDataInterface
{
    /**
     * Eav setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(Product::ENTITY, 'sxe_productno',[
                'group' => 'General',
                'type' => 'varchar',
                'label' => 'SX.e Product Number',
                'input' => 'text',
                'required' => false,
                'sort_order' => 50,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'visible' => true,
                'is_html_allowed_on_front' => false,
                'visible_on_front' => false
            ]
        );

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'sxe_customer_nr',
            [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'SXe Customer Number',
                'sort_order' => 100,
                'position' => 90,
                'required' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'system' => false,
                'visible' => true
            ]
        );

        $select = $customerSetup->getSetup()->getConnection()->select()->from(
            ['ea' => $customerSetup->getSetup()->getTable('eav_attribute')],
            ['attribute_id']
        )->where('ea.entity_type_id',
            Customer::ENTITY
        )->where(
            'ea.attribute_code = ?',
            'sxe_customer_nr'
        );
        /** To save the attribute values from backend we need to save to customer_form_attribute */
        $data = ['form_code' => 'adminhtml_customer', 'attribute_id' => implode('',$customerSetup->getSetup()->getConnection()->fetchCol($select,'attribute_id'))];
        $customerSetup->getSetup()->getConnection()->insertOnDuplicate($customerSetup->getSetup()->getTable('customer_form_attribute'), $data);
    }
}