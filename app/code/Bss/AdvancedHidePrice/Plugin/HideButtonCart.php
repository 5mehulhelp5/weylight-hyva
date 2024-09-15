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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdvancedHidePrice\Plugin;

use Bss\AdvancedHidePrice\Helper\Data;
use Magento\Catalog\Block\Product\View as MagentoView;

/**
 * Class HideButtonCart
 *
 * @package Bss\AdvancedHidePrice\Plugin
 */
class HideButtonCart
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * HideButtonCart constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Hide Add to cart Button
     *
     * @param MagentoView $subject
     * @param mixed $result
     * @return string
     */
    public function afterToHtml(
        MagentoView $subject,
                    $result
    ) {
        $matchedNames = [
            'product.info.addtocart.additional',
            'product.info.addtocart',
            'product.info.addtocart.bundle'
        ];
        $product = $subject->getProduct();
        $buttonCart = $result;
        if (in_array($subject->getNameInLayout(), $matchedNames)) {
            if ($subject->getProduct()->getActiveCallHidePrice()) {
                $this->isActiveCallHidePrice($subject, $result);
            } else {
                $this->hideCartForGroupedProduct($subject, $result);
            }
            if ($product->getTypeId() == 'configurable' && $this->helper->isEnable()) {
                $result = '<div id="advancedhideprice">' . $result . '</div>';
            }
        }

        return $result;
    }

    /**
     * Hide Button Cart For Grouped Product
     *
     * @param object $subject
     * @param object $result
     */
    public function hideCartForGroupedProduct($subject, &$result)
    {
        if ($subject->getProduct()->getTypeId() =='grouped') {
            $checkChildAllCallPrice = true;
            $associateProducts = $subject->getProduct()->getTypeInstance()
                ->getAssociatedProducts($subject->getProduct());
            if (count($associateProducts) > 0) {
                foreach ($associateProducts as $item) {
                    if ($subject->getProduct()->isSaleable()) {
                        if ($item->getActiveCallHidePrice() !== 'callforprice'
                            && $item->getActiveCallHidePrice() !== 'hideprice'
                        ) {
                            $checkChildAllCallPrice = false;
                        }
                    }
                }
                if ($checkChildAllCallPrice) {
                    $result = '';
                }
            }
        }
    }

    /**
     * Is Active Call Hide Price
     *
     * @param object $subject
     * @param object $result
     */
    public function isActiveCallHidePrice($subject, &$result)
    {
        $product = $subject->getProduct();

        if ($subject->getProduct()->getActiveCallHidePrice() == 'callforprice') {
            $result = '<div id="callforprice" class="callforprice-container">' .
                '<input type="hidden" name="product" value="' . $product->getId() . '">' .
                '<input type="hidden" name="product_name" value="' . $product->getName() . '">' .
                '<a class="callforprice_clickme action primary">' .
                $this->helper->getCallforpriceText($product) . '</a>' .
                '</div>';
        } else {
            $result = '<h2 id="callforprice_text" class="callforprice_text">'
                . $this->helper->getCallforpriceText($product) . '</h2>';
        }
        $result .= '<input type="hidden" class="bss-call-for-price">';
    }
}
