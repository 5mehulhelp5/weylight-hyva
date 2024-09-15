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
namespace Bss\AdvancedHidePrice\Block\Adminhtml\Request\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

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
        $this->setId('request_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Inquiry Information'));
    }

    /**
     * @return WidgetTabs|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'request_info',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    \Bss\AdvancedHidePrice\Block\Adminhtml\Request\Edit\Tab\Info::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'request_sendmail',
            [
                'label' => __('Send Mail'),
                'title' => __('Send Mail'),
                'url' => $this->getUrl('*/*/sendmail', ['_current' => true]),
                'class' => 'ajax',
                'active' => false
            ]
        );
    }
}
