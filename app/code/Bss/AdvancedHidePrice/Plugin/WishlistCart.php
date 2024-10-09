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
namespace Bss\AdvancedHidePrice\Plugin;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class WishlistCart
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * WishlistCart constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $pr
     * @param \Magento\Wishlist\Model\ItemFactory $itemFactory
     * @param ResultFactory $resultFactory
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        ResultFactory $resultFactory,
        MessageManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->productRepository = $pr;
        $this->itemFactory = $itemFactory;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Wishlist\Controller\Index\Cart $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\ResultInterface|mixed
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $itemId = $subject->getRequest()->getParam('item');
        $item = $this->itemFactory->create()->load($itemId);
        $product = $this->productRepository->getById($item->getProductId());
        if (!$this->helper->activeCallHidePrice($product)) {
            return $proceed();
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            return $resultRedirect;
        }
    }
}
