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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Plugin\Adminhtml\WidgetTab;

use Magento\Framework\App\RequestInterface;
use Magento\Widget\Model\Widget\Instance;

/**
 * Class MainTab
 * @package Bss\Gallery\Plugin\Adminhtml\WidgetTab
 */
class MainTab
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Instance
     */
    protected $instanceModel;

    /**
     * MainTab constructor.
     * @param RequestInterface $request
     * @param Instance $instanceModel
     */
    public function __construct(
        RequestInterface $request,
        Instance $instanceModel
    ) {
        $this->request = $request;
        $this->instanceModel = $instanceModel;
    }

    /**
     * @param \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main $mainSubject
     * @param \Magento\Framework\Data\Form $form
     * @return array
     */
    public function beforeSetForm(
        \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main $mainSubject,
        \Magento\Framework\Data\Form $form
    ) {
        $fieldSet = $form->getElement('base_fieldset');
        $fieldSet->removeField('sort_order');
        $widgetId = $this->request->getParam('instance_id');
        $widget = $this->instanceModel->load($widgetId);
        if ($widget &&
            $widget->getCode() &&
            $widget->getCode() == 'bss_gallery_widget') {
            $fieldSet->addField(
                'sort_order',
                'text',
                [
                    'name' => 'sort_order',
                    'label' => __('Sort Order'),
                    'title' => __('Sort Order'),
                    'required' => false,
                    'note' => __('Sort Order of widget instances in the same container')
                ]
            );
        }
        return [$form];
    }
}