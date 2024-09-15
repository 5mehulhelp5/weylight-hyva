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
namespace Bss\Gallery\Block\Adminhtml\Item\Edit;

/**
 * Adminhtml item edit form
 *
 * Class Form
 *
 * @package Bss\Gallery\Block\Adminhtml\Item\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Bss\Gallery\Model\Item\Source\Categories
     */
    protected $categories;

    /**
     * Form constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Bss\Gallery\Model\Item\Source\Categories $categories
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Bss\Gallery\Model\Item\Source\Categories $categories,
        array $data = []
    ) {
        $this->categories = $categories;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('item_form');
        $this->setTitle(__('Item Information'));
    }

    /**
     * Create form for gallery item
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Bss\Gallery\Model\Item $model */
        $model = $this->_coreRegistry->registry('gallery_item');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]]
        );

        $form->setHtmlIdPrefix('post_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        $fieldset->addType('image', \Bss\Gallery\Block\Adminhtml\Item\Helper\Image::class);
        if ($model->getItemId()) {
            $fieldset->addField('item_id', 'hidden', ['name' => 'item_id']);
        }
        $fieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Item Title'), 'title' => __('Item Title'), 'required' => true]
        );
        $fieldset->addField(
            'image',
            'image',
            $this->returnConfigImage()
        );
        $fieldset->addField(
            'video',
            'text',
            $this->returnConfigVideo()
        );
        $fieldset->addField(
            'sorting',
            'text',
            [
                'name' => 'sorting',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'class' => 'validate-number validate-digits',
                'required' => false
            ]
        )->getAfterElementHtml();
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
        // Get all the categories that in the database
        $allCategories = $this->categories->toOptionArray();
        $model->setData('category_ids', $this->categories->getCategoryIds());
        $fieldset->addField(
            'category_ids',
            'multiselect',
            [
                'label' => __('Select Albums'),
                'title' => __('Select Albums'),
                'required' => false,
                'name' => 'category_ids[]',
                'values' => $allCategories
            ]
        );
        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('description'),
                'title' => __('description'),
                'style' => 'height:5em',
                'required' => true
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Return config image
     *
     * @return array
     */
    protected function returnConfigImage()
    {
        return [
            'name' => 'image',
            'label' => __('Image'),
            'title' => __('Image'),
            'after_element_html' => $this->returnValidateImageJs(),
            'required' => true
        ];
    }

    /**
     * Return config video
     *
     * @return array
     */
    protected function returnConfigVideo()
    {
        return [
            'name' => 'video',
            'label' => __('Video'),
            'title' => __('Video'),
            'required' => false,
            'after_element_html' => '<small>Show youtube video when click image</small>'
        ];
    }

    /**
     * Return validate image js
     *
     * @return string
     */
    protected function returnValidateImageJs()
    {
        return '<script type="text/x-magento-init">
        {
            "*": {
            "Bss_Gallery/js/add_validate_image":{}
            }
        }
        </script>';
    }
}
