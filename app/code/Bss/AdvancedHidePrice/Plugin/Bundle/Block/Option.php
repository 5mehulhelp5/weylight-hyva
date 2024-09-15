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
namespace Bss\AdvancedHidePrice\Plugin\Bundle\Block;

class Option
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    private $helper;

    /**
     * Option constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function aroundRenderPriceString(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        \Closure $proceed,
        $selection,
        $includeContainer = true
    ) {
        if (($this->helper->activeCallHidePrice($subject->getProduct()) &&
                $subject->getProduct()->getTypeId() == 'bundle') || $this->helper->activeCallHidePrice($selection)) {
            return '';
        }

        return $proceed($selection, $includeContainer);
    }

    /**
     * remove + symbal
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelectionTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $result
    ) {
        $result = str_replace('<span class="price-notice">+</span>', '', $result);

        return $result;
    }

    /**
     * remove + symbal
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelectionQtyTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $result
    ) {
        $result = str_replace('<span class="price-notice">+</span>', '', $result);

        return $result;
    }
}
