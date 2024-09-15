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
 * @category  BSS
 * @package   Hyva_BssAdvancedHidePrice
 * @author    Extension Team
 * @copyright Copyright (c) 2023-present BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Hyva\BssAdvancedHidePrice\Plugin;

use Bss\AdvancedHidePrice\Plugin\CategoryAvancedHidePrice as OriginalCategoryAvancedHidePrice;
use Bss\AdvancedHidePrice\Helper\Data;

// phpcs:disable Generic.Files.LineLength.TooLong

class CategoryAvancedHidePrice extends OriginalCategoryAvancedHidePrice
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;

        parent::__construct(
            $helper
        );
    }

    /**
     * Custom price html
     *
     * @param  \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param  mixed                                         $result
     * @return string
     */
    public function afterToHtml($subject, $result)
    {
        $product = $subject->getSaleableItem();
        $selector = $this->helper->getSelector() ? $this->helper->getSelector() : '[data-role="tocart"],[aria-label="' . __('Add to Cart') . '"]';

        if ($product->getIsActiveCallHidePrice()) {
            $result = '<div id="advancedhideprice_' . $product->getId() . '"></div><div id="advancedhideprice_price' .
                $product->getId() . '"><div class="price-box price-final_price" data-product-id="' .
                $product->getId() . '"></div></div>';

            if ($product->getActiveCallHidePrice() == 'callforprice') {
                $result .= '<div class="bss-hide flex callforprice-container callforprice_' . $product->getId() . '"';
                $result .= '>
                    <input type="hidden" name="product" value="' . $product->getId() . '">
                    <input type="hidden" name="product_name" value="' . $product->getName() . '">
                    <a onclick="openBssCfpDialog(this)"
                        class="callforprice_clickme btn btn-primary justify-center cursor-pointer"
                        aria-label="' . $product->getCallforpriceText() . '">' . $product->getCallforpriceText() . '</a>
                    <script>var bssCallForPriceCartBtn = `' . $selector . '`</script>
                </div>';
            } elseif ($product->getActiveCallHidePrice() == 'hideprice') {
                $result .= '<div class="bss-hide flex callforprice-container callforprice_text_' . $product->getId() . '">' . $product->getCallforpriceText() . '</div>';
                $result .= '<script>var bssCallForPriceCartBtn = `' . $selector . '`</script>';
            }
        } else {
            if ($product->getTypeId() == 'configurable' && $this->helper->isEnable()) {
                $result = '<div id="advancedhideprice_' . $product->getId() . '"></div><div id="advancedhideprice_price'
                    . $product->getId() . '">' . $result . '</div>';
            }

            $result = $result . '<div class="callforprice_show callforprice_show_' . $product->getId() . '"></div>
                <script>var bssShowCartBtn = `' . $selector . '`</script>
                <script>
                    typeof bssShowCartBtnClasses == "undefined" ? window.bssShowCartBtnClasses = [] : null;
                    bssShowCartBtnClasses.push(".callforprice_show_' . $product->getId() . '");
                </script>';
        }
        return $result;
    }
}
