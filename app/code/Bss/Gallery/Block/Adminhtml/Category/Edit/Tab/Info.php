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
 * @package    Bss_Gallery
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Info
 *
 * @package Bss\Gallery\Block\Adminhtml\Category\Edit\Tab
 */
class Info extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $store;

    /**
     * Info constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $store
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $store,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->store = $store;
    }

    /**
     * Prepare form
     *
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Bss\Gallery\Model\Category $model */
        $model = $this->_coreRegistry->registry('gallery_category');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('post_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        $fieldset->addType('image', \Bss\Gallery\Block\Adminhtml\Category\Helper\Image::class);
        if ($model->getCategoryId()) {
            $fieldset->addField('category_id', 'hidden', ['name' => 'category_id']);
        }
        $fieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Album Title'), 'title' => __('Album Title'), 'required' => true]
        );
        $fieldset->addField(
            'category_description',
            'text',
            [
                'name' => 'category_description',
                'label' => __('Album Description'),
                'title' => __('Album Description'),
                'required' => false
            ]
        );
        $fieldset->addField(
            'category_meta_keywords',
            'textarea',
            [
                'name' => 'category_meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
                'required' => false
            ]
        );
        $fieldset->addField(
            'category_meta_description',
            'textarea',
            [
                'name' => 'category_meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
                'required' => false
            ]
        );
        $fieldset->addField(
            'item_layout',
            'select',
            [
                'label' => __('Layout'),
                'title' => __('Layout'),
                'name' => 'item_layout',
                'required' => false,
                'options' => ['1' => __('Standard'), '2' => __('Slider')]
            ]
        );
        $fieldset->addField(
            'slider_auto_play',
            'select',
            [
                'label' => __('Slide Auto Play'),
                'title' => __('Slide Auto Play'),
                'name' => 'slider_auto_play',
                'required' => false,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );
        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }
        $field = $fieldset->addField(
            'store_ids',
            'multiselect',
            [
                'name' => 'store_ids[]',
                'label' => __('Assign to Store Views'),
                'title' => __('Assign to Store Views'),
                'required' => true,
                'values' => $this->store->getStoreValuesForForm(false, true)
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
        );
        $field->setRenderer($renderer);
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Album Info');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Album Info');
    }

    /**
     * Tab can show
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
