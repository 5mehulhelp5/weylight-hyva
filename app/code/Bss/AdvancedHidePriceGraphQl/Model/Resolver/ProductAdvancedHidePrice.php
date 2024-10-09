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

namespace Bss\AdvancedHidePriceGraphQl\Model\Resolver;

use Bss\AdvancedHidePriceGraphQl\Model\CheckLogicCallForHidePrice;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * CLass ProductAdvancedHidePrice
 */
class ProductAdvancedHidePrice implements ResolverInterface
{

    /**
     * @var CheckLogicCallForHidePrice
     */
    protected $checkLogicCallForHidePrice;

    /**
     * Construct
     *
     * @param CheckLogicCallForHidePrice $checkLogicCallForHidePrice
     */
    public function __construct(
        CheckLogicCallForHidePrice $checkLogicCallForHidePrice
    ) {
        $this->checkLogicCallForHidePrice = $checkLogicCallForHidePrice;
    }

    /**
     * Add display callforprice_text and type to response result
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $storeId = $context->getExtensionAttributes()->getStore()->getStoreId();
        $customerGroupId = $context->getExtensionAttributes()->getCustomerGroupId();
        $skuProduct = $value['sku'];
        $moduleConfigAdvancedHidePriceType = $this->checkLogicCallForHidePrice->checkLogicCallForPrice($skuProduct, $customerGroupId, $storeId);
        if (!$moduleConfigAdvancedHidePriceType) {
            return [
                "advancedhideprice_type" => null,
                "advancedhideprice_text" => null
            ];
        }
        $productConfigAdvancedHidePrice = $this->checkLogicCallForHidePrice->getAdvancedHidePriceTextProduct($skuProduct, $customerGroupId, $moduleConfigAdvancedHidePriceType);
        if (($productConfigAdvancedHidePrice || is_null($productConfigAdvancedHidePrice))) {
            return [
                "advancedhideprice_type" => $moduleConfigAdvancedHidePriceType,
                "advancedhideprice_text" => $productConfigAdvancedHidePrice
            ];
        } else {
            return [
                "advancedhideprice_type" => $moduleConfigAdvancedHidePriceType,
                "advancedhideprice_text" => $this->checkLogicCallForHidePrice->getAdvancedHidePriceTextGlobal($moduleConfigAdvancedHidePriceType)
            ];
        }
    }
}
