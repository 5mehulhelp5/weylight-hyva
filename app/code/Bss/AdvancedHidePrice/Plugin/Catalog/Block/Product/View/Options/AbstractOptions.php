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
namespace Bss\AdvancedHidePrice\Plugin\Catalog\Block\Product\View\Options;

class AbstractOptions
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    private $helper;

    /**
     * AbstractOptions constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Hide option price html
     * @param \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetFormattedPrice(
        $subject,
        callable $proceed
    ) {
        if ($this->helper->activeCallHidePrice($subject->getProduct())) {
            return '';
        }

        return $proceed();
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValuesHtml($subject, $result)
    {
        $result = str_replace('<span class="price-notice">+</span>', '', $result);

        return $result;
    }
}
