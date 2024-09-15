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
 * Interface CategoryInterface
 *
 * @package Bss\Gallery\Api\Data
 */
interface CategoryInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const CATEGORY_ID = 'category_id';
    const TITLE = 'title';
    const URL_KEY = 'url_key';
    const THUMBNAIL = 'thumbnail';
    const CREATE_TIME = 'create_time';
    const UPDATE_TIME = 'update_time';
    const IS_ACTIVE = 'is_active';
    const ITEM_IDS = 'Item_ids';
    const STORE_IDS = 'store_ids';

    /**
     * Get cate id
     *
     * @return int
     */
    public function getId();

    /**
     * Get cate title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get cate url key
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Get cate thumbnail
     *
     * @return string
     */
    public function getThumbnail();

    /**
     * Get cate create time
     *
     * @return string
     */
    public function getCreateTime();

    /**
     * Get cate update time
     *
     * @return string
     */
    public function getUpdateTime();

    /**
     * Get cate status
     *
     * @return int
     */
    public function isActive();

    /**
     * Get items in cate
     *
     * @return string
     */
    public function getItemIds();

    /**
     * Set cate id
     *
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * Set url key for cate
     *
     * @param string $urlKey
     * @return void
     */
    public function setUrlKey($urlKey);

    /**
     * Set title for cate
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title);

    /**
     * Set thumbnail for cate
     *
     * @param string $thumbnail
     * @return void
     */
    public function setThumbnail($thumbnail);

    /**
     * Set create time for cate
     *
     * @param string $createTime
     * @return void
     */
    public function setCreateTime($createTime);

    /**
     * Set update time for cate
     *
     * @param string $updateTime
     * @return void
     */
    public function setUpdateTime($updateTime);

    /**
     * Set cate status
     *
     * @param int $isActive
     * @return void
     */
    public function setIsActive($isActive);

    /**
     * Assign for cate
     *
     * @param string $itemIds
     * @return void
     */
    public function setItemIds($itemIds);

    /**
     * Get store view ids
     *
     * @return string
     */
    public function getStoreIds();

    /**
     * Set store view ids
     *
     * @param string $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds);
}
