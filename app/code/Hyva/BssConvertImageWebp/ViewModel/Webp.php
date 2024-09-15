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
 * @package    Hyva_BssConvertImageWebp
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Hyva\BssConvertImageWebp\ViewModel;

use Bss\ConvertImageWebp\Model\Config;
use Bss\ConvertImageWebp\Model\ReplaceUrl;

class Webp implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var ReplaceUrl
     */
    protected $replaceUrl;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ReplaceUrl $replaceUrl
     * @param Config $config
     */
    public function __construct(
        ReplaceUrl $replaceUrl,
        Config $config
    ) {
        $this->config = $config;
        $this->replaceUrl = $replaceUrl;
    }

    /**
     * Replace url image
     *
     * @param string $url
     * @return mixed|string
     */
    public function replaceUrlImage($url)
    {
        try {
            return $this->replaceUrl->replaceUrl($url);
        } catch (\Exception $e) {
            return $url;
        }
    }

    /**
     * Get config
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get Url Webp Medium
     *
     * @param array $images
     * @param mixed $imageHelper
     * @param mixed $mainImage
     * @return mixed|string
     */
    public function getUrlWebpMedium($images, $imageHelper, $mainImage)
    {
        $mainImageData = $mainImage ? $mainImage->getData('medium_image_url') : $imageHelper->getDefaultPlaceholderUrl('image');
        $isEnable = $this->config->isEnableModule();
        if ($isEnable) {
            $urlWebpMedium = $this->replaceUrlImage($mainImageData);
        } else {
            $urlWebpMedium = $mainImageData;
        }
        return $urlWebpMedium;
    }
}
