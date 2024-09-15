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
 * Class Thumb
 *
 * @package Bss\Gallery\Controller\Adminhtml\Category
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Thumb extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Thumb constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\Session $session
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\Session $session,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute set thumbnail
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $status = 'false';
        if ($this->getRequest()->getParam('id') && $this->getRequest()->getParam('keys')) {
            $id = $this->getRequest()->getParam('id');
            $keys = $this->getRequest()->getParam('keys');
            $thumb = ['id' => $id, 'keys' => $keys];
            $this->session->setCategoryThumb($thumb);
            $status = 'true';
        }
        return $this->resultJsonFactory->create()->setData(['status' => $status]);
    }

    /**
     * If is allow to save thumbnail
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Gallery::category_save');
    }
}
