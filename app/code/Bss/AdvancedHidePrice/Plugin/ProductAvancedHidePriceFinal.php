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
 * @package    Bss_AdvancedHidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdvancedHidePrice\Plugin;

use Magento\Bundle\Model\Product\Type;

class ProductAvancedHidePriceFinal
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * ProductAvancedHidePrice constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Catalog\Pricing\Render\PriceBox $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml($subject, $result)
    {
        $page = $this->request->getFullActionName();
        if ($page != "catalog_product_view") {
            return $result;
        }
        $currentProduct = $this->registry->registry('product');
        $productType = $currentProduct->getTypeId();
        if ($productType == Type::TYPE_CODE) {
            $product = $subject->getSaleableItem();
            $activeCallHidePrice = $this->helper->activeCallHidePrice($product);
            if ($activeCallHidePrice == "hideprice" || $activeCallHidePrice == "callforprice") {
                return "";
            }
        }
        return $result;
    }
}
