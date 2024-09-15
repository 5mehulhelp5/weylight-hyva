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
namespace Bss\AdvancedHidePrice\Plugin\Block\Product\View\Type;

class Configurable
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Configurable constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->helper = $helper;
        $this->json = $json;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig($subject, $result)
    {
        if ($this->helper->isEnable()) {
            $childProduct = $this->helper->getAllData($subject->getProduct()->getEntityId());
            $config = $this->json->unserialize($result);
            $config["advancedHidePrice"] = $childProduct;
            return $this->json->serialize($config);
        }
        return $result;
    }
}
