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
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdvancedHidePrice\Plugin\Catalog\Model\Option;

class Value
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    private $helper;

    /**
     * Value constructor.
     *
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Set price = 0 if active call hide price
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $subject
     * @param float|int $result
     * @return int
     */
    public function afterGetPrice(
        \Magento\Catalog\Model\Product\Option\Value $subject,
        $result
    ) {
        if ($this->helper->activeCallHidePrice($subject->getOption()->getProduct())) {
            return 0;
        }

        return $result;
    }
}
