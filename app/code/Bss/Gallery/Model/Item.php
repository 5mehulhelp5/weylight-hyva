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

use Bss\Gallery\Api\Data\ItemInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Item
 *
 * @package Bss\Gallery\Model
 */
class Item extends \Magento\Framework\Model\AbstractModel implements ItemInterface, IdentityInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const CACHE_TAG = 'gallery_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'gallery_item';

    /**
     * @var string
     */
    protected $_eventPrefix = 'gallery_item';

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\Gallery\Model\ResourceModel\Item::class);
    }

    /**
     * Get available status
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
     * Get identifier
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * Get category ids
     *
     * @return string
     */
    public function getCategoryIds()
    {
        return $this->getData(self::CATEGORY_IDS);
    }

    /**
     * Get item title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get item thumbnail
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->getData(self::THUMBNAIL);
    }

    /**
     * Get item image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Get item video
     *
     * @return string
     */
    public function getVideo()
    {
        return $this->getData(self::VIDEO);
    }

    /**
     * Get item type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE_ID);
    }

    /**
     * Get item description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
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
     * Get item status
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set item id
     *
     * @param int $id
     * @return Item|\Magento\Framework\Model\AbstractModel
     */
    public function setId($id)
    {
        return $this->setData(self::ITEM_ID, $id);
    }

    /**
     * Set assigned category ids
     *
     * @param string $categoryIds
     * @return Item
     */
    public function setCategoryIds($categoryIds)
    {
        return $this->setData(self::CATEGORY_IDS, $categoryIds);
    }

    /**
     * Set item title
     *
     * @param string $title
     * @return Item
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set item thumbnail
     *
     * @param string $thumbnail
     * @return Item
     */
    public function setThumbnail($thumbnail)
    {
        return $this->setData(self::THUMBNAIL, $thumbnail);
    }

    /**
     * Set item image
     *
     * @param string $image
     * @return Item
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Set video
     *
     * @param string $video
     * @return Item
     */
    public function setVideo($video)
    {
        return $this->setData(self::VIDEO, $video);
    }

    /**
     * Set item type
     *
     * @param string $typeId
     * @return Item
     */
    public function setType($typeId)
    {
        return $this->setData(self::TYPE_ID, $typeId);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Item
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set create time
     *
     * @param string $createTime
     * @return Item
     */
    public function setCreateTime($createTime)
    {
        return $this->setData(self::CREATE_TIME, $createTime);
    }

    /**
     * Get create time
     *
     * @param string $updateTime
     * @return Item
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set status
     *
     * @param string $isActive
     * @return Item
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }
}
