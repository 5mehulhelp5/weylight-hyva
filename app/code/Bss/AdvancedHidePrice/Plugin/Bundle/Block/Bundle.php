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
namespace Bss\AdvancedHidePrice\Plugin\Bundle\Block;

class Bundle
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
     * Bundle constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->json = $json;
    }

    /**
     * Remove + symbal
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsonConfig(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject,
        $result
    ) {
        $result = $this->json->unserialize($result);
        $arrOptions = $result['options'];
        foreach ($arrOptions as $keyArrOption => $options) {
            foreach ($options as $selection => $option) {
                if ($selection == 'selections') {
                    foreach ($option as $keyOption => $data) {
                        $productId = $data['optionId'];
                        $product = $this->productRepository->getById($productId);
                        if ($this->helper->activeCallHidePrice($product)) {
                            $arrOptions[$keyArrOption][$selection][$keyOption]['prices']['oldPrice']['amount'] = 0;
                            $arrOptions[$keyArrOption][$selection][$keyOption]['prices']['basePrice']['amount'] = 0;
                            $arrOptions[$keyArrOption][$selection][$keyOption]['prices']['finalPrice']['amount'] = 0;
                            $arrOptions[$keyArrOption][$selection][$keyOption]['customQty'] = 0;
                            $arrOptions[$keyArrOption][$selection][$keyOption]['qty'] = 0;
                        }
                    }
                }
            }
        }
        $result['options'] = $arrOptions;
        $result = $this->json->serialize($result);
        return $result;
    }
}
