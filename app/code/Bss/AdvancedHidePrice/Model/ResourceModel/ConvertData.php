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
 * @package    Bss_AdvancedHidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdvancedHidePrice\Model\ResourceModel;

class ConvertData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('core_config_data', 'config_id');
    }

    /**
     * Convert Old Data
     *
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function convertOldData()
    {
        $data = [];
        $connection = $this->getConnection();
        $table_config_core_data = $this->getMainTable('core_config_data');
        $sql = $connection->select()->from($table_config_core_data)
            ->where("path = ? ", 'bss_call_for_price/callforprice_design/form_fields');
        $query = $connection->query($sql);
        while ($row = $query->fetch()) {
            array_push($data, $row);
        }
        return $data;
    }

    /**
     * Convert Old Data To New
     *
     * @param $sql
     * @return \Zend_Db_Statement_Interface
     */
    public function convertOldDataToNew($sql)
    {
        $connection = $this->_resources->getConnection();
        return $connection->query($sql);
    }
}
