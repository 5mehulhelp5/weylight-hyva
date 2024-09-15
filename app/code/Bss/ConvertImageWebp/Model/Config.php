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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConvertImageWebp\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    public const XML_PATH_ENABLE_MODULE = "c_i_w/general/enabled";
    public const XML_PATH_QUALITY = 'c_i_w/general/quality';
    public const XML_PATH_CDN = 'c_i_w/general/cdn';
    public const XML_PATH_ENABLE_WEBP_PAGE_BUILDER = 'c_i_w/general/enable_webp_page_builder';
    public const XML_PATH_IGNORE_FOLDERS = "c_i_w/general/ignore_folders";
    public const XML_PATH_AUTO_GENERATE_WEBP_IMAGE = 'c_i_w/general/auto_generate_webp_image';

    public const XML_PATH_ENABLE_HOMEPAGE = "c_i_w/home_page/enabled";
    public const XML_PATH_IGNORE_IMAGE_HOME_PAGE = "c_i_w/home_page/ignore_images";

    public const XML_PATH_ENABLE_PRODUCT_PAGE = "c_i_w/product_page/enabled";
    public const XML_PATH_IGNORE_IDS_PRODUCT_PAGE = "c_i_w/product_page/ignore_ids";
    public const XML_PATH_IGNORE_IMAGE_PRODUCT_PAGE = "c_i_w/product_page/ignore_images";

    public const XML_PATH_ENABLE_CATEGORY_PAGE = "c_i_w/category_page/enabled";
    public const XML_PATH_IGNORE_IDS_CATEGORY_PAGE = "c_i_w/category_page/ignore_ids";
    public const XML_PATH_IGNORE_IMAGE_CATEGORY_PAGE = "c_i_w/category_page/ignore_images";

    public const XML_PATH_ENABLE_CMS_PAGE = "c_i_w/cms_page/enabled";
    public const XML_PATH_IGNORE_IDS_CMS_PAGE = "c_i_w/cms_page/ignore_ids";
    public const XML_PATH_IGNORE_IMAGE_CMS_PAGE = "c_i_w/cms_page/ignore_images";

    /**
     * @var null|array
     */
    protected $ignoreFolders = null;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface                     $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is enable module
     *
     * @param null|int $websiteId
     * @return bool
     */
    public function isEnableModule($websiteId = null)
    {
        return (int)$this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_MODULE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Is enable convert image webp with image page builder.
     *
     * @param int|string|null $websiteId
     * @return int
     */
    public function isEnableImagePageBuilder($websiteId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_WEBP_PAGE_BUILDER,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Is enable in home page
     *
     * @param null|int $websiteId
     * @return bool
     */
    public function isEnableHomePage($websiteId = null)
    {
        $config = (int)$this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_HOMEPAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->isEnableModule($websiteId) && $config;
    }

    /**
     * Ignore webp in home page
     *
     * @param null|int $websiteId
     * @return false|string[]
     */
    public function ignoreWebpHomePage($websiteId = null)
    {
        $config = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IGNORE_IMAGE_HOME_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->getArrayIgnore($config);
    }

    /**
     * Is enable in  product page
     *
     * @param null|int $websiteId
     * @return bool
     */
    public function isEnableProductPage($websiteId = null)
    {
        $config = (int)$this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_PRODUCT_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->isEnableModule($config) && $config;
    }

    /**
     * Ignore product ids
     *
     * @param null|int $websiteId
     * @return false|string[]
     */
    public function getIgnoreProductIds($websiteId = null)
    {
        $config = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IGNORE_IDS_PRODUCT_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->getArrayIgnore($config);
    }

    /**
     * Ignore images in product page
     *
     * @param null|int $websiteId
     * @return false|string[]
     */
    public function getIgnoreImagesProduct($websiteId = null)
    {
        $config = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IGNORE_IMAGE_PRODUCT_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->getArrayIgnore($config);
    }

    /**
     * Is enable category page
     *
     * @param null|int $websiteId
     * @return bool
     */
    public function isEnableCategoryPage($websiteId = null)
    {
        $config = (int)$this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_CATEGORY_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->isEnableModule() && $config;
    }

    /**
     * Get ignore category ids
     *
     * @param null|int $websiteId
     * @return false|string[]
     */
    public function getIgnoreCategoryIds($websiteId = null)
    {
        $config = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IGNORE_IDS_CATEGORY_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->getArrayIgnore($config);
    }

    /**
     * Get ignore images category
     *
     * @param null|int $websiteId
     * @return false|string[]
     */
    public function getIgnoreImagesCategory($websiteId = null)
    {
        $config = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IGNORE_IMAGE_CATEGORY_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->getArrayIgnore($config);
    }

    /**
     * Is enable cms page
     *
     * @param null|int $websiteId
     * @return bool
     */
    public function isEnableCmsPage($websiteId = null)
    {
        $config = (int)$this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_CMS_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->isEnableModule() && $config;
    }

    /**
     * Get ignore cms ids
     *
     * @param null|int $websiteId
     * @return false|string[]
     */
    public function getIgnoreCmsIds($websiteId = null)
    {
        $config = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IGNORE_IDS_CMS_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->getArrayIgnore($config);
    }

    /**
     * Get ignore images cms
     *
     * @param null|int $websiteId
     * @return false|string[]
     */
    public function getIgnoreImagesCms($websiteId = null)
    {
        $config = (string)$this->scopeConfig->getValue(
            self::XML_PATH_IGNORE_IMAGE_CMS_PAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->getArrayIgnore($config);
    }

    /**
     * Get ignore folders
     *
     * @param null|int $websiteId
     * @return false|string[]
     */
    public function getIgnoreFolders($websiteId = null)
    {
        if (!$this->ignoreFolders) {
            $config = (string)$this->scopeConfig->getValue(
                self::XML_PATH_IGNORE_FOLDERS,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
            $this->ignoreFolders = $this->getArrayIgnore($config);
        }
        return $this->ignoreFolders;
    }

    /**
     * Get auto generate img to webp.
     *
     * @param int|string|null $websiteId
     * @return int
     */
    public function getAutoGenerateWebpImage($websiteId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_AUTO_GENERATE_WEBP_IMAGE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get array ignore
     *
     * @param string $data
     * @return false|string[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getArrayIgnore($data)
    {
        $pathImages = [];
        if ($data) {
            $pathImages = explode(",", $data);
            $baseUrlMedia = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            $baseUrl = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

            if (strpos($baseUrl, "pub/") !== false || strpos($baseUrlMedia, "pub/") === false) {
                $pathImages = $this->replacePub($pathImages);
            }
        }
        return $pathImages;
    }

    /**
     * Replace pub:Compatible with custom url
     *
     * @param array $pathImages
     * @return array
     */
    public function replacePub($pathImages)
    {
        $data = [];
        foreach ($pathImages as $path) {
            if ($path) {
                $path = trim($path);
                $data[] = str_replace("pub/", "", $path);
            }
        }
        return $data;
    }

    /**
     * Get quantity image webp
     *
     * @return int
     */
    public function getQuaLityImageWebp()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_QUALITY);
    }

    /**
     * Get CDN url.
     *
     * @return string
     */
    public function getUrlCDN()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CDN);
    }
}
