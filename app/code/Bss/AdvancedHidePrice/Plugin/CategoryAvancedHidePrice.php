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

class CategoryAvancedHidePrice
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * CategoryAvancedHidePrice constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param mixed $result
     * @return string
     */
    public function afterToHtml($subject, $result)
    {
        $product = $subject->getSaleableItem();
        $selector = $this->helper->getSelector() ? $this->helper->getSelector() : ".action.tocart";
        if ($product->getIsActiveCallHidePrice()) {
            $result = '<div id="advancedhideprice_' . $product->getId() . '"></div><div id="advancedhideprice_price' .
                $product->getId() . '"><div class="price-box price-final_price" data-product-id="' .
                $product->getId() . '"></div></div>';

            if ($product->getActiveCallHidePrice() == 'callforprice') {
                $result .= '<div class="bss-hide callforprice-container callforprice_' . $product->getId() . '"';
                $result .= 'data-mage-init=\'{
                                "Bss_AdvancedHidePrice/js/avanced_price": {
                                    "selector": "' . $selector . '"
                                }
                            }\'';
                $result .= '>
                    <input type="hidden" name="product" value="' . $product->getId() . '">
                    <input type="hidden" name="product_name" value="' . $product->getName() . '">
                    <a class="callforprice_clickme action primary" title="' . $product->getCallforpriceText() . '">' . $product->getCallforpriceText() . '</a>
                </div>
                <script type="text/javascript">
                    require(["jquery"], function($){
                        $(".product-item").trigger("contentUpdated");
                    });                    
                </script>';
            } elseif ($product->getActiveCallHidePrice() == 'hideprice') {
                $result .= '<div class="bss-hide callforprice-container callforprice_text_' . $product->getId() . '"';
                $result .= 'data-mage-init=\'{
                                "Bss_AdvancedHidePrice/js/avanced_price": {
                                    "selector": "' . $selector . '"
                                }
                            }\'';
                $result .= '>' . $product->getCallforpriceText() . '</div>';
            }
        } else {
            if ($product->getTypeId() == 'configurable' && $this->helper->isEnable()) {
                $result = '<div id="advancedhideprice_' . $product->getId() . '"></div><div id="advancedhideprice_price'
                    . $product->getId() . '">' . $result . '</div>';
            }

            $result = $result . '<div class="callforprice_show callforprice_show_' . $product->getId() . '"></div>
                <script type="text/x-magento-init">
                    {
                        ".callforprice_show_' . $product->getId() . '": {
                            "Bss_AdvancedHidePrice/js/show_tocart": {
                                "selector" : "' . $selector . '"
                            }
                        }
                    }
                </script>';
        }
        return $result;
    }
}
