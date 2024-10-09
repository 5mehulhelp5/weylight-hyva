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
use Magento\Store\Model\ScopeInterface;

/**
 * Class Category
 *
 * @package Bss\Gallery\Helper
 */
class Category extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = 'bss_gallery/general/enable';
    const XML_PATH_ALBUM_TITLE = 'bss_gallery/general/album_title';
    const XML_PATH_IMAGE_PER_PAGE = 'bss_gallery/general/image_per_page';
    const XML_PATH_LAYOUT_TYPE = 'bss_gallery/general/layout_type';
    const XML_PATH_AUTO_LOAD = 'bss_gallery/general/autoload';
    const XML_PATH_PAGE_SPEED = 'bss_gallery/general/page_speed';
    const XML_PATH_TITLE_POSITION = 'bss_gallery/general/title_position';
    const XML_PATH_TRANSITION_EFFECT = 'bss_gallery/general/transition_effect';
    const XML_PATH_ITEM_LAYOUT_TYPE = 'bss_gallery/general/item_layout_type';

    /**
     * @var \Bss\Gallery\Model\Category
     */
    protected $category;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Bss\Gallery\Model\Category $category
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Bss\Gallery\Model\Category $category,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->category = $category;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * If config is enable in front end
     *
     * @return string
     */
    public function isEnabledInFrontend()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get title of album
     *
     * @return string
     */
    public function getAlbumTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ALBUM_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get item per page
     *
     * @return string
     */
    public function getItemPerPage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IMAGE_PER_PAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get type of layout
     *
     * @return string
     */
    public function getLayoutType()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LAYOUT_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is auto load
     *
     * @return string
     */
    public function isAutoLoad()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AUTO_LOAD,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get page speed
     *
     * @return string
     */
    public function getPageSpeed()
    {
        $speed = $this->scopeConfig->getValue(
            self::XML_PATH_PAGE_SPEED,
            ScopeInterface::SCOPE_STORE
        );
        if (!$speed) {
            $speed = (int) 5000;
        }
        return $speed;
    }

    /**
     * Get title position
     *
     * @return string
     */
    public function getTitlePosition()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TITLE_POSITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get transition effect
     *
     * @return string
     */
    public function getTransitionEffect()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TRANSITION_EFFECT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return a gallery category from given category id.
     *
     * @param Action $action
     * @param int $categoryId
     * @return \Magento\Framework\View\Result\Page|bool
     */
    public function prepareResultCategory(Action $action, $categoryId = null)
    {
        if ($categoryId !== null && $categoryId !== $this->category->getId()) {
            $delimiterPosition = strrpos($categoryId, '|');
            if ($delimiterPosition) {
                $categoryId = substr($categoryId, 0, $delimiterPosition);
            }
            if (!$this->category->load($categoryId)) {
                return false;
            }
        }
        if (!$this->category->getId()) {
            return false;
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('gallery_category_view');
        $resultPage->addPageLayoutHandles(['id' => $this->category->getId()]);
        $this->_eventManager->dispatch(
            'bss_gallery_category_render',
            ['category' => $this->category, 'controller_action' => $action]
        );
        return $resultPage;
    }
}
