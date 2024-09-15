<?php
namespace Bss\ProductTags\Plugin\Block\Widget\Grid;

class Serializer extends \Magento\Backend\Block\Widget\Grid\Serializer
{
    /**
     * Set input element name
     *
     * @return \Magento\Backend\Block\Widget\Grid\Serializer|void
     */
    public function _beforeToHtml()
    {
        if ($this->getRequest()->getParam('products_related')) {
            $this->setInputElementName("bss_input");
        }
        parent::_beforeToHtml();
    }
}
