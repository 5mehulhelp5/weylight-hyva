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

class ProductAvancedHidePrice
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
     * ProductAvancedHidePrice constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper=$helper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Pricing\Render\PriceBox $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml($subject, $result)
    {
        $page=$this->request->getFullActionName();
        if ($page != "catalog_product_view") {
            return $result;
        }
        $product = $subject->getSaleableItem();
        if ($this->helper->activeCallHidePrice($product)) {
            return "";
        }
        return $result;
    }
}
