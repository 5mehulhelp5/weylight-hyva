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
namespace Bss\ConvertImageWebp\Plugin\Catalog\Block\Product\View;

use Bss\ConvertImageWebp\Model\Config;
use Bss\ConvertImageWebp\Model\Convert;
use Bss\ConvertImageWebp\Model\ReplaceUrl;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Serialize\SerializerInterface;

class Gallery
{
    /**
     * @var ReplaceUrl
     */
    protected $replaceUrl;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Convert
     */
    protected $convert;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Gallery constructor.
     *
     * @param ReplaceUrl $replaceUrl
     * @param SerializerInterface $serializer
     * @param Convert $convert
     * @param Config $config
     */
    public function __construct(
        ReplaceUrl $replaceUrl,
        SerializerInterface $serializer,
        Convert $convert,
        Config $config
    ) {
        $this->replaceUrl = $replaceUrl;
        $this->serializer = $serializer;
        $this->convert = $convert;
        $this->config = $config;
    }

    /**
     * Add web images into Gallery images json
     *
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param string $galleryImagesJson
     * @return bool|string
     */
    public function afterGetGalleryImagesJson(\Magento\Catalog\Block\Product\View\Gallery $subject, string $galleryImagesJson)
    {
        if ($this->config->isEnableModule()) {
            $jsonData = $this->serializer->unserialize($galleryImagesJson);
            $jsonData = $this->addWebImages($jsonData);
            return $this->serializer->serialize($jsonData);
        }

        return $galleryImagesJson;
    }

    /**
     * Add Web Images
     *
     * @param array $images
     * @return array
     */
    public function addWebImages($images)
    {
        foreach ($images as $id => $imageData) {
            if (isset($imageData['thumb'])) {
                $imageData['thumb_webp'] = $this->getWebpUrl($imageData['thumb']);
            }
            if (isset($imageData['img'])) {
                $imageData['img_webp'] = $this->getWebpUrl($imageData['img']);
            }
            if (isset($imageData["full"])) {
                $imageData['full_webp'] = $this->getWebpUrl($imageData['full']);
            }
            $images[$id] = $imageData;
        }
        return $images;
    }

    /**
     * Get webp url
     *
     * @param string $url
     * @return string
     */
    public function getWebpUrl($url)
    {
        try {
            return $this->replaceUrl->replaceUrl($url);
        } catch (\Exception $e) {
            return $url;
        }
    }

    /**
     * Check & Convert link default img to link webp img.
     *
     * @param string $url
     * @param string|int $imageId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws FileSystemException
     */
    public function convertLinkImgWebp($url, $imageId)
    {
        $linkImgWebp = $this->replaceUrl->handleProductPage($url, $imageId);
        $folderImgWebp = $this->convert->getSourceImageUri($linkImgWebp);

        if ($this->convert->checkExistsFile($folderImgWebp) // If img webp exist.
            || $this->convert->isImageFisrtLoadCache($linkImgWebp) // If img webp first load because not img cache(.../jpg/wp01_black_black.webp).
        ) {
            return $linkImgWebp;
        }

        return $url;
    }
}
