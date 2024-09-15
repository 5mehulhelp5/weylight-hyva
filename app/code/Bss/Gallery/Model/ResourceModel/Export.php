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
namespace Bss\Gallery\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Class Export
 *
 * @package Bss\Gallery\Model\ResourceModel
 */
class Export
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * Export constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
    }

    /**
     * Get album table
     *
     * @return \Zend_Db_Statement_Interface
     */
    public function getAlbumTable()
    {
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('bss_gallery_category')],
                [
                    '*'
                ]
            )->order(['main_table.category_id']);
        $review = $this->readAdapter->query($select);
        return $review;
    }

    /**
     * Get item table
     *
     * @return \Zend_Db_Statement_Interface
     */
    public function getItemTable()
    {
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('bss_gallery_item')],
                [
                    '*'
                ]
            )->order(['main_table.item_id']);
        $review = $this->readAdapter->query($select);
        return $review;
    }

    /**
     * Get table name
     *
     * @param string $entity
     * @return bool|string
     */
    protected function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * Format a date
     *
     * @param string $dateTime
     * @return string
     */
    public function formatDate($dateTime)
    {
        $dateTimeAsTimeZone = $this->timezone
            ->date($dateTime)
            ->format('YmdHis');
        return $dateTimeAsTimeZone;
    }

    /**
     * Export albums
     *
     * @param \Zend_Db_Statement_Interface $albums
     * @return array
     */
    public function getExportAlbums($albums)
    {
        $data[0] = ['Album Id', 'Album Title', 'Album Description', 'Meta Key', 'Meta Description',
            'Layout', 'Auto Play', 'Status', 'Item Ids'];
        foreach ($albums as $album) {
            $row = [];

            $row[] = $album['category_id'];
            $row[] = $album['title'];
            $row[] = $album['category_description'];
            $row[] = $album['category_meta_keywords'];
            $row[] = $album['category_meta_description'];
            $row[] = $album['item_layout'];
            $row[] = $album['slider_auto_play'];
            $row[] = $album['is_active'];
            $row[] = $album['Item_ids'];

            $data[] = $row;
        }
        return $data;
    }

    /**
     * Export items
     *
     * @param \Zend_Db_Statement_Interface $items
     * @return array
     */
    public function getExportItems($items)
    {
        $data[0] = ['Item Id', 'Item Name', 'Item Description', 'Image Path', 'Video Url',
            'Sort Order', 'Status', 'Album Ids'];

        foreach ($items as $item) {
            $row = [];

            $row[] = $item['item_id'];
            $row[] = $item['title'];
            $row[] = $item['description'];
            $row[] = $item['image'];
            $row[] = $item['video'];
            $row[] = $item['sorting'];
            $row[] = $item['is_active'];
            $row[] = $item['category_ids'];

            $data[] = $row;
        }
        return $data;
    }
}
