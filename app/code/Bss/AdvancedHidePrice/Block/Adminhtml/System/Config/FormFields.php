<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AdvancedHidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdvancedHidePrice\Block\Adminhtml\System\Config;

class FormFields extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Bss_AdvancedHidePrice::system/config/form/field/array.phtml';

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObject;

    /**
     * @var FormFields\Checkbox
     */
    protected $checkboxRenderer;

    /**
     * @var FormFields\Select
     */
    protected $selectRenderer;

    /**
     * FormFields constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param FormFields\Checkbox $checkboxRenderer
     * @param FormFields\Select $selectRenderer
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Bss\AdvancedHidePrice\Block\Adminhtml\System\Config\FormFields\Checkbox $checkboxRenderer,
        \Bss\AdvancedHidePrice\Block\Adminhtml\System\Config\FormFields\Select $selectRenderer,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->dataObject = $objectFactory;
        $this->checkboxRenderer = $checkboxRenderer;
        $this->selectRenderer = $selectRenderer;
        parent::__construct($context);
    }

    /**
     * Prepare Form
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'field_label',
            [
                'label' => __('Label'),
                'renderer' => false,
                'style' => 'width:100px',
                'class' => 'input-text admin__control-text validate-no-empty'
            ]
        );
        $this->addColumn(
            'field_type',
            [
                'label' => __('Type'),
                'renderer' => $this->selectRenderer,
                'style' => 'width:100px',
                'class' => 'select admin__control-select'
            ]
        );
        $this->addColumn(
            'field_order',
            [
                'label' => __('Order'),
                'renderer' => false,
                'class' => 'validate-per-page-value-list input-text admin__control-text'
            ]
        );
        $this->addColumn(
            'field_required',
            [
                'label' => __('Required'),
                'renderer' => $this->checkboxRenderer,
                'class' => 'checkbox_required checkbox_field'
            ]
        );
        $this->addColumn(
            'field_enable',
            [
                'label' => __('Enable'),
                'renderer' => $this->checkboxRenderer,
                'class' => 'checkbox_enable checkbox_field'
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getArrayRows()
    {
        $result = [];
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();
        if ($element->getValue() && is_array($element->getValue())) {
            foreach ($element->getValue() as $rowId => $row) {
                $row['field_required'] = isset($row['field_required']) ? $row['field_required'] : '0';
                $row['field_enable'] = isset($row['field_enable']) ? $row['field_enable'] : '0';
                $rowColumnValues = [];
                foreach ($row as $key => $value) {
                    $row[$key] = $value;
                    $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key];
                }
                $row['_id'] = $rowId;
                $row['column_values'] = $rowColumnValues;
                $result[$rowId] = $this->dataObject->create()->setData($row);
            }
        }
        return $result;
    }
}
