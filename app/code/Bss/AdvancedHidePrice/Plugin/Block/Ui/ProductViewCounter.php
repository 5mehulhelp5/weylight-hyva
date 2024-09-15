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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdvancedHidePrice\Plugin\Block\Ui;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Registry;
use Bss\AdvancedHidePrice\Helper\Data;
use Magento\Framework\Data\Helper\PostHelper;

class ProductViewCounter
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * ProductViewCounter constructor.
     * @param Data $helper
     * @param SerializerInterface $serialize
     * @param Registry $registry
     * @param PostHelper $postHelper
     */
    public function __construct(
        Data $helper,
        SerializerInterface $serialize,
        Registry $registry,
        PostHelper $postHelper
    ) {
        $this->helper = $helper;
        $this->serialize = $serialize;
        $this->registry = $registry;
        $this->postHelper = $postHelper;
    }

    /**
     * @param \Magento\Catalog\Block\Ui\ProductViewCounter $subject
     * @param string $result
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCurrentProductData(
        $subject,
        $result
    ) {
        $product = $this->registry->registry('product');
        if ($product && $productId = $product->getId()) {
            if ($this->helper->activeCallHidePrice($product)) {
                $currentProductData = $this->serialize->unserialize($result);
                $currentProductData['items'][$productId]['price_info']['callhide_price'] = true;
                return $this->serialize->serialize($currentProductData);
            }
        }
        return $result;
    }
}
