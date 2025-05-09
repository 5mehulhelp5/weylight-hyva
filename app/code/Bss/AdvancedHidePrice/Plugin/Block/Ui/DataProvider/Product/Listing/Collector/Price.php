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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdvancedHidePrice\Plugin\Block\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Bss\AdvancedHidePrice\Helper\Data;
use Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Price as CollectorPrice;

class Price
{
    /**
     * @var PriceInfoInterfaceFactory
     */
    private $priceInfoFactory;

    /**
     * @var FormattedPriceInfoBuilder
     */
    private $formattedPriceInfoBuilder;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Price constructor.
     * @param PriceInfoInterfaceFactory $priceInfoFactory
     * @param FormattedPriceInfoBuilder $formattedPriceInfoBuilder
     * @param Data $helper
     */
    public function __construct(
        PriceInfoInterfaceFactory $priceInfoFactory,
        FormattedPriceInfoBuilder $formattedPriceInfoBuilder,
        Data $helper
    ) {
        $this->priceInfoFactory = $priceInfoFactory;
        $this->formattedPriceInfoBuilder = $formattedPriceInfoBuilder;
        $this->helper = $helper;
    }

    /**
     * @param CollectorPrice $subject
     * @param callable $proceed
     * @param ProductInterface $product
     * @param ProductRenderInterface $productRender
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollect(
        CollectorPrice $subject,
        callable $proceed,
        ProductInterface $product,
        ProductRenderInterface $productRender
    ) {
        if ($this->helper->activeCallHidePrice($product)
        ) {
            $priceInfo = $productRender->getPriceInfo();

            if (!$productRender->getPriceInfo()) {
                /** @var PriceInfoInterface $priceInfo */
                $priceInfo = $this->priceInfoFactory->create();
            }

            $this->formattedPriceInfoBuilder->build(
                $priceInfo,
                $productRender->getStoreId(),
                $productRender->getCurrencyCode()
            );

            $productRender->setPriceInfo($priceInfo);
            return $this;
        }
        $proceed($product, $productRender);
    }
}
