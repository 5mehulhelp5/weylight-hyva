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
namespace Bss\ConvertImageWebp\Plugin;

use Bss\ConvertImageWebp\Model\Config;
use Bss\ConvertImageWebp\Model\Convert;
use Bss\ConvertImageWebp\Model\ReplaceUrl;
use Magento\Store\Model\StoreManagerInterface;

class Block
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Convert
     */
    protected $convert;

    /**
     * @var ReplaceUrl
     */
    protected $replaceUrl;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Block constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Convert $convert
     * @param ReplaceUrl $replaceUrl
     * @param Config $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Bss\ConvertImageWebp\Model\Convert $convert,
        \Bss\ConvertImageWebp\Model\ReplaceUrl $replaceUrl,
        \Bss\ConvertImageWebp\Model\Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->convert = $convert;
        $this->replaceUrl = $replaceUrl;
        $this->config = $config;
    }

    /**
     * Replace url image to url image webp
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @param string $html
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterToHtml(\Magento\Framework\View\Element\AbstractBlock $block, $html)
    {
        if ($this->config->isEnableModule() && $html) {
            //Convert and Show webp image in tag <img>.
            if (strpos($html, "img") !== false
                && (strpos($html, "pagebuilder") === false || $this->config->isEnableImagePageBuilder()) // Check img in page-builder.
            ) {
                $htmlNew = $html;
                preg_match_all('/<img[^>]+>/i', $htmlNew, $imgTags);
                if (count($imgTags[0])) {
                    for ($i = 0; $i < count($imgTags[0]); $i++) {
                        // get the source string
                        preg_match('/src="([^"]+)/i', $imgTags[0][$i], $image);

                        // remove opening 'src=' tag, can`t get the regex right
                        if (isset($image[0])) {
                            $imageUrlOld = str_ireplace('src="', '', $image[0]);
                            $imageUrl = $this->addFullUrl($imageUrlOld);
                            if ($this->convert->isImageFile($imageUrl)) {
                                preg_match_all('/<img[^>]+>/i', $html, $imgConvertTags);

                                $imageUrlSource = $this->replaceUrl->replaceUrl($imageUrl);
                                foreach ($imgConvertTags[0] as $imgTag) {
                                    $html = $this->convertTagImg($html, $imgTag, $imageUrl, $imageUrlSource);
                                }
                            }
                        }
                    }
                }
            }

            //Convert and Show webp image in tag <div> of page builder.
            if ($this->config->isEnableImagePageBuilder()
                && (strpos($html, "pagebuilder-slider") !== false || strpos($html, "pagebuilder-banner") !== false)
            ) {
                preg_match_all('/data-background-images=.\{[^<>]+\}./', $html, $dataImage);
                if (isset($dataImage[0])) {
                    foreach ($dataImage[0] as $backgroundData) {
                        preg_match_all('/http[^"\']+[a-z]/', $backgroundData, $imgOlds);
                        if (isset($imgOlds[0])) {
                            foreach ($imgOlds[0] as $imgOld) {
                                $imageUrlWebp = $this->replaceUrl->replaceUrl($imgOld);
                                if (substr($imageUrlWebp, -strlen(".webp")) === ".webp" && $imgOld != $imageUrlWebp) {
                                    $html = $this->convertBackgroundImg($html, $imgOld, $imageUrlWebp);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $html;
    }

    /**
     * Convert tag <img> to <picture>
     *
     * @param string|mixed $html
     * @param string $imgTag
     * @param string $imageUrl
     * @param string $imageUrlSource
     * @return array|mixed|string|string[]
     */
    public function convertTagImg($html, $imgTag, $imageUrl, $imageUrlSource)
    {
        // Skip the <img> to <picture> conversion
        if (strpos($imageUrlSource, '.webp') === false // if the image is ignored
            && strpos($imageUrlSource, Convert::FOLDER_IMAGE_PRODUCT) === false // if img is not a product image.
        ) {
            return $html;
        }

        if (strpos($imgTag, 'bss-converted') === false) {
            $srcStart = 'src="';
            $srcEnd = '"';

            $strStart = (int)strpos($imgTag, $srcStart) + strlen($srcStart);
            $strEnd = (int)strpos($imgTag, $srcEnd, $strStart) - $strStart;
            $imgNotConvert = substr($imgTag, $strStart, $strEnd);

            if (strpos($imageUrl, $imgNotConvert) !== false) {
                $strTypeImg = explode(".", $imageUrlSource);

                $sourceTag = str_replace('<img', '<source', $imgTag);
                $sourceTag = str_replace($srcStart . $imgNotConvert . $srcEnd, 'srcset="' . $imageUrlSource . '"', $sourceTag);
                $sourceTag = str_replace('>', ' type="image/' . end($strTypeImg) . '">', $sourceTag);

                $imgTagConvert = str_replace('>', ' bss-converted>', $imgTag);

                $result = '<picture>' . $sourceTag . $imgTagConvert . '</picture>';
                $html = str_replace($imgTag, $result, $html);
            }
        }

        return $html;
    }

    /**
     * Replace img old to img webp.
     *
     * @param string $html
     * @param string $imageUrl
     * @param string $imageUrlWebp
     * @return string
     */
    public function convertBackgroundImg($html, $imageUrl, $imageUrlWebp)
    {
        try {
            return str_replace($imageUrl, $imageUrlWebp, $html);
        } catch (\Exception $e) {
            return $html;
        }
    }

    /**
     * Add full url
     *
     * @param string $url
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addFullUrl($url)
    {
        $webBaseUrl = $this->convert->getUrlBaseWeb();
        $cdnUrl = $this->config->getUrlCDN();

        if (strpos($url, $webBaseUrl) === 0
            || ($cdnUrl && strpos($url, $cdnUrl) === 0)
        ) {
            return $url;
        }

        if (strpos($url, "//") === 0) {
            try {
                return $this->storeManager->getStore()->getUrl($url);
            } catch (\Exception $exception) {
                return $url;
            }
        }

        if (strpos($url, "/") === 0) {
            return $webBaseUrl . substr($url, 1);
        }

        return $webBaseUrl . $url;
    }
}
