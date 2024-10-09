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
namespace Bss\Gallery\Helper;

use Magento\Framework\App\Action\Action;

/**
 * Class Item
 *
 * @package Bss\Gallery\Helper
 */
class Item extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\Gallery\Model\Item
     */
    protected $item;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Item constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Bss\Gallery\Model\Item $item
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Bss\Gallery\Model\Item $item,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->item = $item;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Return a gallery item from given item id.
     *
     * @param Action $action
     * @param int $itemId
     * @return \Magento\Framework\View\Result\Page|bool
     */
    public function prepareResultItem(Action $action, $itemId = null)
    {
        if ($itemId !== null && $itemId !== $this->item->getId()) {
            $delimiterPosition = strrpos($itemId, '|');
            if ($delimiterPosition) {
                $itemId = substr($itemId, 0, $delimiterPosition);
            }
            if (!$this->item->load($itemId)) {
                return false;
            }
        }
        if (!$this->item->getId()) {
            return false;
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('gallery_item_view');
        $resultPage->addPageLayoutHandles(['id' => $this->item->getId()]);
        $this->_eventManager->dispatch(
            'bss_gallery_item_render',
            ['item' => $this->item, 'controller_action' => $action]
        );
        return $resultPage;
    }
}
