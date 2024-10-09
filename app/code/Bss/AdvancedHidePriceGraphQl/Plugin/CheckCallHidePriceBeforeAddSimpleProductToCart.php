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

use Bss\AdvancedHidePriceGraphQl\Model\CheckLogicCallForHidePrice;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\AddProductsToCart;

/**
 * CheckCallHidePriceBeforeAddSimpleProductToCart
 */
class CheckCallHidePriceBeforeAddSimpleProductToCart
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
    public function __construct(CheckLogicCallForHidePrice $checkLogicCallForHidePrice)
    {
        $this->checkLogicCallForHidePrice=$checkLogicCallForHidePrice;
    }

    /**
     * Add logic call for price add to cart
     *
     * @param AddProductsToCart $subject
     * @param Quote $cart
     * @param array $cartItems
     * @return array
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function beforeExecute(
        AddProductsToCart $subject,
        Quote $cart,
        array $cartItems
    ) {
        $customerGroupId = $cart->getCustomerGroupId();
        $storeId = $cart->getStoreId();
        foreach ($cartItems as $item) {
            $advancedHidePrice = $this->checkLogicCallForHidePrice->checkLogicCallForPrice($item['data']['sku'], $customerGroupId, $storeId);
            if ($advancedHidePrice) {
                throw new GraphQlInputException(__($this->checkLogicCallForHidePrice->getAdvancedHidePriceTextGlobal($advancedHidePrice)));
            }
        }
        return [$cart, $cartItems];
    }
}
