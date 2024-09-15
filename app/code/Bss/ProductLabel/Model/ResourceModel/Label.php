<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductLabel\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Label extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('bss_productlabel_label', 'id');
    }

    /**
     * @param int $productId
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getIndexedProduct($productId)
    {
        $connection = $this->_resources->getConnection();
        $labelFlatTable = $this->_resources->getTableName('bss_productlabel_flat');
        $data = [];

        $sql = $connection->select()->from(
            $labelFlatTable,
            ['*']
        )->where('product_id = ?', $productId);

        $query = $connection->query($sql);
        while ($row = $query->fetch()) {
            array_push($data, $row);
        }

        return $data;
    }
}
