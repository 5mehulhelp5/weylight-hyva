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
namespace Bss\Gallery\Api\Data;

/**
 * Interface ItemInterface
 *
 * @package Bss\Gallery\Api\Data
 */
interface ItemInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ITEM_ID = 'item_id';
    const CATEGORY_IDS = 'category_ids';
    const TITLE = 'title';
    const IMAGE = 'image';
    const VIDEO = 'video';
    const DESCRIPTION = 'description';
    const CREATE_TIME = 'create_time';
    const UPDATE_TIME = 'update_time';
    const IS_ACTIVE = 'is_active';
    const TYPE_ID = 'type_id';
    const THUMBNAIL = 'thumbnail';

    /**
     * Get item identifier
     *
     * @return int
     */
    public function getId();

    /**
     * Get assigned identifiers of cate
     *
     * @return string
     */
    public function getCategoryIds();

    /**
     * Get item title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get item thumbnail
     *
     * @return string
     */
    public function getThumbnail();

    /**
     * Get item image
     *
     * @return string
     */
    public function getImage();

    /**
     * Get video
     *
     * @return string
     */
    public function getVideo();

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get create time
     *
     * @return string
     */
    public function getCreateTime();

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime();

    /**
     * Get status of item
     *
     * @return int
     */
    public function isActive();

    /**
     * Set identifier for item
     *
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * Assign item to a cate identifiers
     *
     * @param string $categoryIds
     * @return void
     */
    public function setCategoryIds($categoryIds);

    /**
     * Set item title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title);

    /**
     * Set thumbnail for item
     *
     * @param string $thumbnail
     * @return void
     */
    public function setThumbnail($thumbnail);

    /**
     * Set image for item
     *
     * @param string $image
     * @return void
     */
    public function setImage($image);

    /**
     * Set video
     *
     * @param string $video
     * @return void
     */
    public function setVideo($video);

    /**
     * Set item type
     *
     * @param string $typeId
     * @return void
     */
    public function setType($typeId);

    /**
     * Set description
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description);

    /**
     * Set create time
     *
     * @param string $createTime
     * @return void
     */
    public function setCreateTime($createTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return void
     */
    public function setUpdateTime($updateTime);

    /**
     * Set status for item
     *
     * @param int $isActive
     * @return void
     */
    public function setIsActive($isActive);
}
