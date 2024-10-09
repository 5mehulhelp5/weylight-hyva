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
 * @package   Bss_ProductLabelGraphQl
 * @author    Extension Team
 * @copyright Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductLabelGraphQl\Model\Resolver;

use Bss\ProductLabel\Helper\Data as HelperData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class Config get module config
 */
class Config implements ResolverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Config constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Get information config module by store view
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $storeId = $context->getExtensionAttributes()->getStore()->getId();
        return [
            "active" => $this->helperData->isEnable($storeId),
            "batch_size" => $this->helperData->getBatchSize($storeId),
            "display_multiple_label" => $this->helperData->isDisplayMultipleLabel($storeId),
            "display_only_out_of_stock_label" => $this->helperData->isEnableOnlyOutOfStockLabel($storeId),
            "not_display_label_on" => $this->helperData->isNotDisplayOn($storeId),
            "selector_product_list" => $this->helperData->getSelectorProductList($storeId),
            "selector_product_page" => $this->helperData->getSelectorProductPage($storeId)
        ];
    }
}
