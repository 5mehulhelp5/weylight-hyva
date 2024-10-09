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
namespace Bss\Gallery\Block;

use Bss\Gallery\Api\Data\CategoryInterface;
use Bss\Gallery\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\UrlInterface;

/**
 * Class ListCategoryGallery
 *
 * @package Bss\Gallery\Block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListCategoryGallery extends \Bss\Gallery\Block\Base implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Bss\Gallery\Model\ResourceModel\Category\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * ListCategoryGallery constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\Gallery\Helper\Data $helper
     * @param \Bss\Gallery\Helper\Category $dataHelper
     * @param \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory
     * @param \Bss\Gallery\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param UrlInterface $urlInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\Gallery\Helper\Data $helper,
        \Bss\Gallery\Helper\Category $dataHelper,
        \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Bss\Gallery\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $coreRegistry,
        UrlInterface $urlInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $helper,
            $dataHelper,
            $categoryCollectionFactory,
            $itemCollectionFactory,
            $categoryFactory,
            $coreRegistry,
            $urlInterface,
            $data
        );
        $this->storeManager = $storeManager;
    }

    /**
     * Get limit item
     *
     * @return string
     */
    public function getLimit()
    {
        return $this->dataHelper->getItemPerPage();
    }

    /**
     * Get current store view
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get list gallery
     *
     * @return \Bss\Gallery\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategories()
    {
        // Check if categories has already been defined
        // makes our block nice and re-usable! We could
        // pass the 'categories' data to this block, with a collection
        // that has been filtered differently!
        if (!$this->hasData('categories')) {
            $categories = $this->categoryCollectionFactory
                ->create()
                ->addFilter('is_active', 1)
                ->addOrder(
                    CategoryInterface::CREATE_TIME,
                    CategoryCollection::SORT_ORDER_ASC
                );
            $this->setData('categories', $categories);
        }
        return $this->getData('categories');
    }

    /**
     * Get first item in category
     *
     * @return false|\Bss\Gallery\Model\ResourceModel\Item\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFirstCategoryItems()
    {
        $category = $this->getFirtItemCollection();
        $item_ids = explode(',', $category->getData('Item_ids') ?? '');
        $limit = $this->getItemPerPage();
        if ($item_ids != '' && $item_ids != null) {
            $itemCollection = $this->itemCollectionFactory->create();
            $itemCollection->addFieldToSelect('*')->addFieldToFilter(
                'item_id',
                ['in' => $item_ids]
            )->addFieldToFilter('is_active', ['eq' => 1]);
            $itemCollection->setOrder('sorting', 'ASC');
            $itemCollection->setPageSize($limit);
            return $itemCollection;
        }
        return false;
    }

    /**
     * Count item in category
     *
     * @param \Bss\Gallery\Model\Category $category
     * @return int|string
     */
    public function countItems($category)
    {
        $item_ids = $category->getData('Item_ids');
        if ($item_ids != null) {
            return count(explode(',', $item_ids));
        }
        return '0';
    }

    /**
     * Get category collection
     *
     * @return \Bss\Gallery\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->getCategories();

        }
        return $this->collection;
    }

    /**
     * Get collection of first item
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFirtItemCollection()
    {
        return $this->getCollection()->getFirstItem();
    }

    /**
     * Count item in category
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function countFirstCategoryItems()
    {
        $category = $this->getFirtItemCollection();
        $item_ids = $category->getData('Item_ids');
        if ($item_ids != null) {
            return count(explode(',', $item_ids));
        }
        return '0';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Bss\Gallery\Model\Category::CACHE_TAG . '_' . 'list'];
    }

    /**
     * Get item image url
     *
     * @param string $imageName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemImageUrl($imageName)
    {
        $imageUrl = $this->getMediaUrl() . $this->itemDir . '/image' . $imageName;
        if ($imageName && $this->helper->hasImageSize($imageUrl)) {
            return $imageUrl;
        }
        return $this->getViewFileUrl('Bss_Gallery::images/default-image.jpg');
    }

    /**
     * Get type of layout
     *
     * @return string
     */
    public function getLayoutType()
    {
        return $this->dataHelper->getLayoutType();
    }

    /**
     * Get item per page
     *
     * @return string
     */
    public function getItemPerPage()
    {
        return $this->dataHelper->getItemPerPage();
    }

    /**
     * Get transition effect
     *
     * @return string
     */
    public function getTransitionEffect()
    {
        return $this->dataHelper->getTransitionEffect();
    }

    /**
     * Get base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlInterface->getBaseUrl();
    }

    /**
     * Get album title
     *
     * @return string
     */
    public function getAlbumTitle()
    {
        return $this->dataHelper->getAlbumTitle();
    }
}
