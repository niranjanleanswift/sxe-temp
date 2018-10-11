<?php

namespace LeanSwift\EconnectSXE\Block\Backend\Mapping;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ProductMetadataInterface;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    const SXE_VERSION = '1.0.0';

    protected $_ProductMetadataInterface;
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'LeanSwift_EconnectSXE::system/config/version/info.phtml';

    public function __construct
    (   Context $context,
        ProductMetadataInterface $ProductMetadataInterface,
        array $data = []
    ){
        $this->_ProductMetadataInterface = $ProductMetadataInterface;
        parent::__construct($context, $data);
    }

    public function getMagentoVersion()
    {
        $majorMinorVersion = $this->_ProductMetadataInterface->getVersion();
        return $this->_ProductMetadataInterface->getName() . '/' .
                $majorMinorVersion . ' (' .
                $this->_ProductMetadataInterface->getEdition() . ')';
    }

    public function getIONVersion() {
        return '';
    }

    public function getEconnectVersion() {
        return '';
    }

    public function getSXEversion() {
        return self::SXE_VERSION;
    }

    /**
     * Render fieldset html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $columns = $this->getRequest()->getParam('website') || $this->getRequest()->getParam('store') ? 5 : 4;
        return $this->_decorateRowHtml($element, "<td colspan='{$columns}'>" . $this->toHtml() . '</td>');
    }
}
