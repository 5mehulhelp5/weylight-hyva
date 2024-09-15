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
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\AddProductsToCart;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

/**
 * CheckCallHidePriceBeforeAddProductToCart
 */
class CheckCallHidePriceBeforeAddProductToCart
{
    /**
     * @var CheckLogicCallForHidePrice
     */
    protected $checkLogicCallForHidePrice;

    /**
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * Construct
     *
     * @param CheckLogicCallForHidePrice $checkLogicCallForHidePrice
     * @param CustomerGroup $customerGroup
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        CheckLogicCallForHidePrice      $checkLogicCallForHidePrice,
        CustomerGroup                   $customerGroup,
        CartRepositoryInterface         $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
        $this->checkLogicCallForHidePrice = $checkLogicCallForHidePrice;
        $this->customerGroup = $customerGroup;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * Check logic before add product to cart
     *
     * @param AddProductsToCart $subject
     * @param string $maskedCartId
     * @param array $cartItems
     * @return array
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     */
    public function beforeExecute(
        AddProductsToCart $subject,
        string            $maskedCartId,
        array             $cartItems
    ) {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        $cart = $this->cartRepository->get($cartId);
        $customerGroupId = $cart->getCustomerGroupId();
        $storeId = $cart->getStoreId();
        foreach ($cartItems as $item) {
            $advancedHidePrice = $this->checkLogicCallForHidePrice->checkLogicCallForPrice($item->getSku(), $customerGroupId, $storeId);
            if ($advancedHidePrice) {
                throw new GraphQlInputException(__($this->checkLogicCallForHidePrice->getAdvancedHidePriceTextGlobal($advancedHidePrice)));
            }
        }
        return [$maskedCartId, $cartItems];
    }
}
