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

namespace Bss\AdvancedHidePriceGraphQl\Model;

use Bss\AdvancedHidePrice\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Check logic call hide price
 */
class CheckLogicCallForHidePrice
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $data;

    /**
     * Construct
     *
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param Data $data
     */
    public function __construct(Context $context, ProductRepository $productRepository, Data $data)
    {
        $this->context = $context;
        $this->productRepository = $productRepository;
        $this->data = $data;
    }

    /**
     * Check customer query is applying hide price ?
     *
     * @param string $skuInput
     * @param string $customerGroupId
     * @param string|null $storeId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkLogicCallForPrice($skuInput, $customerGroupId, $storeId = null)
    {
        $product = $this->getProduct($skuInput);
        if ($product->getTypeId() === "configurable") {
            $checkLogic = $this->data->activeCallHidePriceConfigurable($product, $storeId, $customerGroupId);
        } else {
            $checkLogic = $this->data->activeCallHidePrice($product, $storeId, $customerGroupId);
        }
        return $checkLogic;
    }

    /**
     * Get Product
     *
     * @param string $skuInput
     * @return ProductInterface|Product|object
     * @throws NoSuchEntityException
     */
    public function getProduct($skuInput)
    {
        return $this->productRepository->get($skuInput);
    }

    /**
     * Get call for price text or hide price text follow config
     *
     * @param string $advancedHidePrice
     * @return string
     */
    public function getAdvancedHidePriceTextGlobal($advancedHidePrice)
    {
        if ($advancedHidePrice == "callforprice") {
            $callHideText = $this->data->callForPriceText();
        } else {
            $callHideText = $this->data->callHidePriceText();
        }
        return $callHideText;
    }

    /**
     * Check product has config callforprice
     * If true return callforprice_text config in product
     * False -> Return fasle
     *
     * @param string $skuInput
     * @param string $customerGroupId
     * @param string $moduleConfigAdvancedHidePriceType
     * @return bool|array
     * @throws NoSuchEntityException
     */
    public function getAdvancedHidePriceTextProduct($skuInput, $customerGroupId, $moduleConfigAdvancedHidePriceType)
    {
        $product = $this->getProduct($skuInput);
        $customerGroupProduct = $this->getCustomeGroup($product, $moduleConfigAdvancedHidePriceType);
        if (
            (
                $product->getData('callforprice_type') == 2
                || $product->getData('callforprice_type') == 1
            )
            &&
            (
                in_array($customerGroupId, $customerGroupProduct)
            )
        ) {
            return $product->getData('callforprice_text');
        }
        return false;
    }

    /**
     * Prepare customer group when in product not config customer group
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $moduleConfigAdvancedHidePriceType
     * @return array
     */
    public function getCustomeGroup($product, $moduleConfigAdvancedHidePriceType)
    {
        if ($product->getData('callforprice_customergroup') == "-1") {
            if ($moduleConfigAdvancedHidePriceType == "hideprice") {
                $customerGroupProduct = explode(',', $this->data->getHidePriceCustomers() ?? "");
            } else {
                $customerGroupProduct = explode(',', $this->data->getCallForPriceCustomers() ?? "");
            }
        } else {
            $customerGroupProduct = explode(',', $product->getData('callforprice_customergroup') ?? "");
        }
        return $customerGroupProduct;
    }
}
