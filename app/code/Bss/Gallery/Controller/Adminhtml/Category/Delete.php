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

use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 *
 * @package Bss\Gallery\Controller\Adminhtml\Category
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\Gallery\Model\CategoryFactory
     */
    protected $bssCategoryFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Cache|CacheTypeListInterface
     */
    private $cache;

    /**
     * Delete constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory
     * @param LoggerInterface $logger
     * @param Cache $cache
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory,
        LoggerInterface $logger,
        CacheTypeListInterface $cache
    ) {
        parent::__construct($context);
        $this->bssCategoryFactory = $bssCategoryFactory;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('category_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->bssCategoryFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The album has been deleted.'));
                $this->cache->invalidate('full_page');
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['category_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a album to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * If is allow to delete
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Gallery::category_delete');
    }
}
