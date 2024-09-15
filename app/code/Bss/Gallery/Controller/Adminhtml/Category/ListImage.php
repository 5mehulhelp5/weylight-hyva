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
namespace Bss\Gallery\Controller\Adminhtml\Category;

/**
 * Class ListImage
 *
 * @package Bss\Gallery\Controller\Adminhtml\Category
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class ListImage extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget
{
    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $html = $this->_view->getLayout()->createBlock(
            \Bss\Gallery\Block\Adminhtml\Category\Edit\Tab\ListImage::class,
            'bss.gallery.category.edit.tab.listimage'
        )->setImages($this->getRequest()->getPost('images', null))->toHtml();

        $html .= $this->_view->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Grid\Serializer::class,
            'crosssell_grid_serializer',
            [ 'data' => [
                'input_names' => 'sorting',
                'grid_block' => 'bss.gallery.category.edit.tab.listimage',
                'callback' => 'getSelectedItems',
                'input_element_name' => 'category_image',
                'reload_param_name' => 'images']
            ]
        )->toHtml();
        $html .= $this->_view->getLayout()->createBlock(
            \Bss\Gallery\Block\Adminhtml\Category\Edit\Tab\ListImageObject::class,
            'bss.gallery.category.edit.tab.listimage.after'
        )->toHtml();
        $this->getResponse()->setBody($html);
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Gallery::category_listimage');
    }
}
