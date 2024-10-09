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

namespace Bss\AdvancedHidePrice\Plugin\Catalog\Model\Product\Type;

use Bss\AdvancedHidePrice\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

/**
 * Class AbstractType
 *
 * @package Bss\AdvancedHidePrice\Plugin\Catalog\Model\Product\Type
 */
class AbstractType
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * AbstractType constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * After Prepare For Cart Advanced
     *
     * @param object $subject
     * @param string|array $result
     * @param DataObject $buyRequest
     * @param Product $product
     * @param null $processMode
     * @return string|array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareForCartAdvanced(
        $subject,
        $result,
        DataObject $buyRequest,
        Product $product,
        $processMode = null
    ) {
        if (is_string($result)) {
            return $result;
        }
        if (is_array($result) && $this->helper->isEnable() && $product->getTypeId() == 'grouped') {
            foreach ($result as $key => $item) {
                if ($item->getActiveCallHidePrice() == 'callforprice'
                    || $item->getActiveCallHidePrice() == 'hideprice') {
                    unset($result[$key]);
                }
            }
        }
        return  $result;
    }
}
