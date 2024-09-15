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
 * @package    Bss_AdvancedHidePriceGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdvancedHidePriceGraphQl\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor\AttributeProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * AddAttributes
 */
class AddAttributes
{
    /**
     * Prepare add attribute to product and query with graphql
     *
     * @param AttributeProcessor $subject
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return array
     */
    public function beforeProcess(
        AttributeProcessor      $subject,
        Collection              $collection,
        SearchCriteriaInterface $searchCriteria,
        array                   $attributeNames,
        ContextInterface        $context = null
    ) {
        $collection->addAttributeToSelect("advancedhideprice_text");
        $collection->addAttributeToSelect("advancedhideprice_type");
        return [$collection, $searchCriteria, $attributeNames, $context];
    }
}
