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
namespace Bss\Gallery\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * Class Tabs
 *
 * @package Bss\Gallery\Block\Adminhtml\Category\Edit
 */
class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('category_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Album Information'));
    }

    /**
     * Create layout
     *
     * @return WidgetTabs|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'category_info',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    \Bss\Gallery\Block\Adminhtml\Category\Edit\Tab\Info::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'category_image',
            [
                'label' => __('Manage Item'),
                'title' => __('Manage Item'),
                'url' => $this->getUrl('*/*/listimage', ['_current' => true]),
                'class' => 'ajax',
                'active' => false
            ]
        );
    }
}
