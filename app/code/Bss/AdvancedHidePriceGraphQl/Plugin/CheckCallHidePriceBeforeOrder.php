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
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;

/**
 * CheckCallHidePriceBeforeOrder
 */
class CheckCallHidePriceBeforeOrder
{

    /**
     * @var CheckLogicCallForHidePrice
     */
    protected $checkLogicCallForHidePrice;

    /**
     * @var GetCartForUser
     */
    protected GetCartForUser $getCartForUser;

    /**
     * Construct
     *
     * @param CheckLogicCallForHidePrice $checkLogicCallForHidePrice
     * @param GetCartForUser $getCartForUser
     */
    public function __construct(
        CheckLogicCallForHidePrice                      $checkLogicCallForHidePrice,
        GetCartForUser                                  $getCartForUser,
    ) {
        $this->checkLogicCallForHidePrice = $checkLogicCallForHidePrice;
        $this->getCartForUser = $getCartForUser;
    }

    /**
     * Before place order, check in cart is owner product is hide price
     * If true , don't place order
     * If false, can order
     *
     * @param PlaceOrder $subject
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws NoSuchEntityException
     * @throws GraphQlInputException
     * @throws GraphQlAuthorizationException
     */
    public function beforeResolve(
        PlaceOrder       $subject,
        Field            $field,
        ContextInterface $context,
        ResolveInfo      $info,
        array            $value = null,
        array            $args = null
    ) {
        $maskedCartId = $args['input']['cart_id'];
        $userId = (int)$context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $userId, $storeId);
        $cartItems = $cart->getData('items');
        $customerGroupId = $context->getExtensionAttributes()->getCustomerGroupId();
        foreach ($cartItems as $item) {
            $sku = $item->getSku();
            if ($item->getProductType() === 'bundle') {
                $sku = $item->getProduct()->getData("sku");
            }
            $advancedHidePrice = $this->checkLogicCallForHidePrice->checkLogicCallForPrice($sku, $customerGroupId, $storeId);
            if ($advancedHidePrice) {
                throw new GraphQlInputException(__($this->checkLogicCallForHidePrice->getAdvancedHidePriceTextGlobal($advancedHidePrice)));
            }
        }
        return [$field, $context, $info, $value, $args];
    }
}
