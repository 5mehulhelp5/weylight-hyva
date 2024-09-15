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
namespace Bss\Gallery\Controller\CateView;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @package Bss\Gallery\Controller\CateView
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends Action
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Bss\Gallery\Helper\Category
     */
    protected $categoryHelper;

    /**
     * @var \Bss\Gallery\Model\CategoryFactory
     */
    protected $bssCategoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param Registry $registry
     * @param \Bss\Gallery\Helper\Category $categoryHelper
     * @param \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Registry $registry,
        \Bss\Gallery\Helper\Category $categoryHelper,
        \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->categoryHelper = $categoryHelper;
        $this->bssCategoryFactory = $bssCategoryFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Return category page view
     *
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $currentStoreId = (int) $this->storeManager->getStore()->getId();
        $categoryId = $this->getRequest()->getParam('category_id', $this->getRequest()->getParam('id', false));
        $category = $this->bssCategoryFactory->create()->load($categoryId);
        $cateStoreIds = $category->getStoreIds();
        $resultPage = $this->categoryHelper->prepareResultCategory($this, $categoryId);
        $foundCate = in_array($currentStoreId, explode(',', $cateStoreIds ?? '')) ||
                        in_array(0, explode(',', $cateStoreIds ?? '')) ? true : false;
        if (!$resultPage || !$foundCate) {
            $this->messageManager->addErrorMessage('The request for this album is not exist!');
            return $this->resultRedirectFactory->create()->setUrl('/gallery');
        }
        $this->registry->register('category', $category);
        $resultPage->getConfig()->getTitle()->set(__('Gallery Album View'));
        $resultPage->getConfig()->setKeyWords($category->getCategoryMetaKeywords());
        $resultPage->getConfig()->setDescription($category->getCategoryMetaDescription());
        // Add breadcrumb
        /** @var \Magento\Theme\Block\Html\Breadcrumbs */
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link' => $this->_url->getUrl('')
                ]
            );
            $breadcrumbs->addCrumb(
                'gallery_category',
                [
                    'label' => __('Gallery'),
                    'title' => __('Gallery'),
                    'link' => $this->_url->getUrl('gallery')
                ]
            );
            $breadcrumbs->addCrumb(
                'gallery_item',
                [
                    'label' => __('Image'),
                    'title' => __('Image')
                ]
            );
            return $resultPage;
        }
        return $resultPage;
    }
}
