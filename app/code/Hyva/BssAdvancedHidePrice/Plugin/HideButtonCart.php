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

use Bss\AdvancedHidePrice\Plugin\HideButtonCart as OriginalHideButtonCart;
use Bss\AdvancedHidePrice\Helper\Data;
use Magento\Catalog\Block\Product\View as MagentoView;

// phpcs:disable Generic.Files.LineLength.TooLong

class HideButtonCart extends OriginalHideButtonCart
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * HideButtonCart constructor.
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
     * Hide Add to cart Button
     *
     * @param  MagentoView $subject
     * @param  mixed       $result
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
        if (in_array($subject->getNameInLayout(), $matchedNames)) {
            if ($product->getActiveCallHidePrice()) {
                $this->isActiveCallHidePrice($subject, $result);
            } else {
                $this->hideCartForGroupedProduct($subject, $result);
            }
            if ($product->getTypeId() == 'configurable' && $this->helper->isEnable()) {
                $result = '<div id="advancedhideprice">' . $result . '</div>';
            }
        }

        if ($subject->getNameInLayout() == 'product.info.quantity' && $product->getActiveCallHidePrice()) {
            return '';
        }

        if ($subject->getNameInLayout() == 'product.info.bundle.price' && $product->getActiveCallHidePrice()) {
            return '';
        }

        return $result;
    }

    /**
     * Is Active Call Hide Price
     *
     * @param MagentoView $subject
     * @param string      $result
     *
     * @return string
     */
    public function isActiveCallHidePrice($subject, &$result)
    {
        $product = $subject->getProduct();

        if ($subject->getProduct()->getActiveCallHidePrice() == 'callforprice') {
            $result = '<div id="callforprice" class="callforprice-container">' .
                '<input type="hidden" name="product" value="' . $product->getId() . '">' .
                '<input type="hidden" name="product_name" value="' . $product->getName() . '">' .
                '<a  onclick="openBssCfpDialog(this)" class="callforprice_clickme action primary btn btn-primary cursor-pointer" aria-label="' . $this->helper->getCallforpriceText($product) . '">' .
                $this->helper->getCallforpriceText($product) . '</a>' .
                '</div>';
        } else {
            $result = '<h2 id="callforprice_text" class="callforprice_text">'
                . $this->helper->getCallforpriceText($product) . '</h2>';
        }
        $result .= '<input type="hidden" class="bss-call-for-price">';
    }
}
