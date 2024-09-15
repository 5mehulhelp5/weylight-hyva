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
 * @package    Bss_ConvertImageWebp
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ConvertImageWebp\Block\Product\Renderer;

use Bss\ConvertImageWebp\Model\Config;
use Bss\ConvertImageWebp\Plugin\Catalog\Block\Product\View\Gallery;
use Magento\Framework\Serialize\SerializerInterface;

class Configurable
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Gallery
     */
    protected $gallery;

    /**
     * Construct.
     *
     * @param SerializerInterface $serializer
     * @param Config $config
     * @param Gallery $gallery
     */
    public function __construct(
        SerializerInterface $serializer,
        Config $config,
        Gallery $gallery
    ) {
        $this->serializer = $serializer;
        $this->config = $config;
        $this->gallery = $gallery;
    }

    /**
     * Apply webp for child configurable product
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param string $result
     * @return bool|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJsonConfig(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $result)
    {
        try {
            $data = $this->serializer->unserialize($result);
            if ($this->config->isEnableModule() && isset($data['images'])) {
                foreach ($data['images'] as $imageId => $image) {
                    foreach ($image as $imageKey => $imageData) {
                        $data['images'][$imageId][$imageKey]['thumb_webp_child'] = $this->gallery->convertLinkImgWebp($imageData['thumb'], $imageId);
                        $data['images'][$imageId][$imageKey]['img_webp_child'] = $this->gallery->convertLinkImgWebp($imageData['img'], $imageId);
                        $data['images'][$imageId][$imageKey]['full_webp_child'] = $this->gallery->convertLinkImgWebp($imageData['full'], $imageId);
                    }
                }
            }

            return $this->serializer->serialize($data);
        } catch (\Exception $e) {
            return $result;
        }
    }
}
