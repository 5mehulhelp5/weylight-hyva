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

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallData constructor.
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();

        $tableName = $setup->getTable('bss_productlabel_label');
        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            // Sample data
            $data = [
                [
                    'name' => 'Trending',
                    'active' => 0
                ],
                [
                    'name' => 'Limited',
                    'active' => 0
                ],
                [
                    'name' => 'Best Choice',
                    'active' => 0
                ],
                [
                    'name' => 'Top 3',
                    'active' => 0
                ],
                [
                    'name' => 'Top 5',
                    'active' => 0
                ],
                [
                    'name' => '10% sale',
                    'active' => 0
                ],
                [
                    'name' => '20% sale',
                    'active' => 0
                ],
                [
                    'name' => '50% sale',
                    'active' => 0
                ],
                [
                    'name' => '90% sale',
                    'active' => 0
                ],
                [
                    'name' => 'Out of stock',
                    'apply_outofstock_product' => true,
                    'active' => 0
                ],
            ];

            // Insert data to table
            $setup->getConnection()->insertMultiple($tableName, $data);
        }

        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'label_data',
            [
                'type' => 'text',
                'input' => 'text',
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'visible' => false,
                'is_html_allowed_on_front' => false,
                'visible_on_front' => false,
                'default' => '',
                'label' => 'Bss Product Label data'
            ]
        );

        $setup->endSetup();
    }
}
