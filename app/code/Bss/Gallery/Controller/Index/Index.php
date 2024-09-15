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
namespace Bss\Gallery\Controller\Index;

use Magento\Framework\App\Action\Action;

/**
 * Class Index
 *
 * @package Bss\Gallery\Controller\Index
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends Action
{
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;

    /**
     * @var \Bss\Gallery\Helper\Category
     */
    protected $categoryHelper;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\Gallery\Helper\Category $categoryHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\Gallery\Helper\Category $categoryHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryHelper = $categoryHelper;
        parent::__construct($context);
    }

    /**
     * Gallery Index, shows a list of categories.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('List Gallery Album'));
        if (!$this->categoryHelper->isEnabledInFrontend()) {
            $this->_forward('defaultNoRoute');
        }
        // Add breadcrumb
        /** @var \Magento\Theme\Block\Html\Breadcrumbs */
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if (!$breadcrumbs) {
            return $resultPage;
        }
        $breadcrumbs->addCrumb(
            'home',
            [
            'label' => __('Home'),
            'title' => __('Home'),
            'link' => $this->_url->getUrl('')]
        );
        $breadcrumbs->addCrumb(
            'gallery_category',
            [
            'label' => __('Gallery'),
            'title' => __('Gallery')]
        );
        return $resultPage;
    }
}
