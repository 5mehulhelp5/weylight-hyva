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
namespace Bss\Gallery\Model;

use Bss\Gallery\Api\Data\CategoryInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Category
 *
 * @package Bss\Gallery\Model
 */
class Category extends \Magento\Framework\Model\AbstractModel implements CategoryInterface, IdentityInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const CACHE_TAG = 'gallery_category';

    /**
     * @var string
     */
    protected $_cacheTag = 'gallery_category';

    /**
     * @var string
     */
    protected $_eventPrefix = 'gallery_category';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ResourceModel\Category
     */
    protected $resoureCategory;

    /**
     * Category constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param ResourceModel\Category $resoureCategory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\Gallery\Model\ResourceModel\Category $resoureCategory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->resoureCategory = $resoureCategory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\Gallery\Model\ResourceModel\Category::class);
    }

    /**
     * Get last category id
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastCategoryId()
    {
        return $this->resoureCategory->getLastCategoryId();
    }

    /**
     * Check url key
     *
     * @param string $urlKey
     * @param int|null $id
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkUrlKey($urlKey, $id = null)
    {
        return $this->resoureCategory->checkUrlKey($urlKey, $id);
    }

    /**
     * Get available statuses
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get ids
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get url key
     *
     * @return string
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilder->getUrl('gallery/' . $this->getUrlKey());
    }

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->getData(self::THUMBNAIL);
    }

    /**
     * Get create time
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->getData(self::CREATE_TIME);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get cate status
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Get id of items
     *
     * @return string
     */
    public function getItemIds()
    {
        return $this->getData(self::ITEM_IDS);
    }

    /**
     * Set cate id
     *
     * @param int $id
     * @return Category|\Magento\Framework\Model\AbstractModel
     */
    public function setId($id)
    {
        return $this->setData(self::CATEGORY_ID, $id);
    }

    /**
     * Set cate title
     *
     * @param string $title
     * @return Category
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set url key
     *
     * @param string $urlKey
     * @return Category
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return Category
     */
    public function setThumbnail($thumbnail)
    {
        return $this->setData(self::THUMBNAIL, $thumbnail);
    }

    /**
     * Set create time
     *
     * @param string $createTime
     * @return Category
     */
    public function setCreateTime($createTime)
    {
        return $this->setData(self::CREATE_TIME, $createTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return Category
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set cate status
     *
     * @param int $isActive
     * @return Category
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set items for cate
     *
     * @param string $itemIds
     * @return Category
     */
    public function setItemIds($itemIds)
    {
        return $this->setData(self::ITEM_IDS, $itemIds);
    }

    /**
     * Get store view ids
     *
     * @return string
     */
    public function getStoreIds()
    {
        return $this->getData(self::STORE_IDS);
    }

    /**
     * Set store view ids
     *
     * @param string $storeIds
     * @return CategoryInterface|Category
     */
    public function setStoreIds($storeIds)
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }
}
