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

namespace Bss\ProductLabel\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(
        SchemaSetupInterface $installer,
        ModuleContextInterface $context
    ) {
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.1.2', '<') &&
            !$installer->tableExists('bss_productlabel_flat')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('bss_productlabel_flat')
            )
                ->addColumn(
                    'label_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false],
                    'Label Id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'store_views',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Store view'
                )
                ->addColumn(
                    'image',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Image'
                )
                ->addColumn(
                    'image_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Image data: width, height, angle, left-percent, top-percent'
                )
                ->addColumn(
                    'customer_groups',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Customer groups'
                )
                ->addColumn(
                    'valid_start_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'Start date apply'
                )
                ->addColumn(
                    'valid_end_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'Valid end date'
                )
                ->addColumn(
                    'priority',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['default' => 0],
                    'Priority'
                )
                ->addIndex(
                    $installer->getIdxName('bss_productlabel_flat', ['label_id']),
                    ['label_id']
                )
                ->addIndex(
                    $installer->getIdxName('bss_productlabel_flat', ['product_id']),
                    ['product_id']
                )
                ->setComment('Product Label Flat Table');

            $installer->getConnection()->createTable($table);
        }

        /**
         * Delete column 'product_ids' from Label table
         */
        $labelTable = $installer->getTable('bss_productlabel_label');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($labelTable) == true) {
            $connection = $installer->getConnection();
            if ($connection->tableColumnExists($labelTable, 'product_ids')) {
                $connection->dropColumn($labelTable, 'product_ids');
            }
        }

        $installer->endSetup();
    }
}
