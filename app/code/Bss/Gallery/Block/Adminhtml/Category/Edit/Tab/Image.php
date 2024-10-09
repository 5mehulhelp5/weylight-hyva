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

namespace Bss\Gallery\Block\Adminhtml\Category\Edit\Tab;

/**
 * Class Image
 *
 * @package Bss\Gallery\Block\Adminhtml\Category\Edit\Tab
 */
class Image extends \Magento\Backend\Block\Template
{
    /**
     * @var
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInt;

    /**
     * Image constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonEncoder
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder,
        \Magento\Catalog\Model\Session $catalogSession,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->catalogSession = $catalogSession;
        $this->urlInt = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    /**
     * Get identifier of block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Bss\Gallery\Block\Adminhtml\Category\Edit\Tab\ListImage::class
            );
        }
        return $this->blockGrid;
    }

    /**
     * Get grid html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/listimage', ['_current' => true]);
    }

    /**
     * Get id of thumb item
     *
     * @return string
     */
    public function getItemThumbId()
    {
        $thumb = $this->catalogSession->getCategoryThumb();
        $keys = $this->catalogSession->getKeySession();
        $category = $this->registry->registry('gallery_category');
        if ($thumb && $keys && $thumb['keys'] == $keys) {
            $id = $thumb['id'];
        } else {
            $id = $category->getItemThumbId();
        }

        return $id;
    }

    /**
     * Get section key
     *
     * @return string
     */
    public function getKeySession()
    {
        $keys = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20);
        $this->catalogSession->setKeySession($keys);
        return $keys;
    }

    /**
     * Create object
     *
     * @return \Magento\Catalog\Model\Session
     */
    public function createObj()
    {
        return $this->catalogSession;
    }

    /**
     * Create url
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function createUrl()
    {
        return $this->urlInt;
    }
}
