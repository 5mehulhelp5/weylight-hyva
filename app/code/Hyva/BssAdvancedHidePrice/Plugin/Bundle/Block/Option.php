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

namespace Hyva\BssAdvancedHidePrice\Plugin\Bundle\Block;

use Bss\AdvancedHidePrice\Plugin\Bundle\Block\Option as OriginalOption;

// phpcs:disable Generic.Files.LineLength.TooLong

class Option extends OriginalOption
{
    /**
     * Remove + symbal
     *
     * @param                                         \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param                                         mixed                                                         $result
     * @return                                        mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelectionTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $result
    ) {
        $result = str_replace('<span class="price-notice">+</span>', '', $result);
        $result = str_replace('+', '', $result);

        return $result;
    }

    /**
     * Remove + symbal
     *
     * @param                                         \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param                                         mixed                                                         $result
     * @return                                        mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelectionQtyTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $result
    ) {
        $result = str_replace('<span class="price-notice">+</span>', '', $result);
        $result = str_replace('+', '', $result);

        return $result;
    }
}
