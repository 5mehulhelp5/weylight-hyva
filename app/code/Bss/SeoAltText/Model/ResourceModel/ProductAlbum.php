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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoAltText\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ProductAlbum
 * @package Bss\SeoAltText\Model\ResourceModel
 */
class ProductAlbum extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('catalog_product_entity_varchar', 'value_id');
    }

    /**
     * @param string $oldPath
     * @param string $newPath
     * @return $this
     */
    public function updateValue($oldPath, $newPath)
    {
        $connection = $this->getConnection();
        $where = ['value=?' => $oldPath];
        $entityVarcharTable = $this->getTable('catalog_product_entity_varchar');
        $mediaGalleryTable = $this->getTable('catalog_product_entity_media_gallery');
        $dataUpdate = ['value' => $newPath];
        $connection->update($entityVarcharTable, $dataUpdate, $where);
        $connection->update($mediaGalleryTable, $dataUpdate, $where);
        return $this;
    }

    /**
     * Save multiple data alt tag in database.
     *
     * @param array $dataChange
     * @param array $attributeAlt
     * @return void
     */
    public function saveMultipleData($dataChange, $attributeAlt)
    {
        $connection = $this->getConnection();
        $conditions = [];

        foreach ($dataChange as $productId => $alt) {
            $case = $connection->quoteInto('?', $productId);
            $result = $connection->quoteInto('?', $alt);
            $conditions[$case] = $result;
        }

        $value = $connection->getCaseSql('entity_id', $conditions);

        $whereTBVarchar = ['entity_id IN (?)' => array_keys($dataChange), 'attribute_id IN (?)' => $attributeAlt];
        $whereTBMedia = ['entity_id IN (?)' => array_keys($dataChange)];

        try {
            $connection->beginTransaction();
            $connection->update($this->getTable('catalog_product_entity_varchar'), ['value' => $value], $whereTBVarchar);
            $connection->update($this->getTable('catalog_product_entity_media_gallery_value'), ['label' => $value], $whereTBMedia);
            $connection->commit();
        } catch(\Exception $e) {
            $connection->rollBack();
        }
    }
}
