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
namespace Bss\ConvertImageWebp\Model;

class ReplaceUrl
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Convert
     */
    protected $convert;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * ReplaceUrl constructor.
     * @param Config $config
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Convert $convert
     */
    public function __construct(
        \Bss\ConvertImageWebp\Model\Config $config,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\ConvertImageWebp\Model\Convert $convert
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->convert = $convert;
    }

    /**
     * Handle replace url webp in home page
     *
     * @param string $url
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException|\Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleHomePage($url)
    {
        if ($this->config->isEnableHomePage()) {
            $ignoreNameImage = $this->config->ignoreWebpHomePage();
            if ($this->checkIgnoreNameImage($ignoreNameImage, $url)) {
                return $url;
            }
            return $this->convert->convertUrlFolder($url);
        }
        return $url;
    }

    /**
     * Handle replace url webp in product page
     *
     * @param string $url
     * @param string|int|null $productId
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException|\Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleProductPage($url, $productId = null)
    {
        if ($this->config->isEnableProductPage()) {
            $id = $productId ?: $this->request->getParam("id");
            $ignoreIds = $this->config->getIgnoreProductIds();
            $ignoreNameImage = $this->checkIgnoreNameImage($this->config->getIgnoreImagesProduct(), $url);
            if (($id && $ignoreIds && in_array($id, $ignoreIds)) || $ignoreNameImage) {
                return $url;
            }

            return $this->convert->convertUrlFolder($url);
        }
        return $url;
    }

    /**
     * Handle replace url webp in category page
     *
     * @param string $url
     * @param string|int|null $idCategory
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException|\Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleCategoryPage($url, $idCategory = null)
    {
        if ($this->config->isEnableCategoryPage()) {
            $idCategory = $idCategory ?: $this->request->getParam("id");
            $ignoreIdsCategory = $this->config->getIgnoreCategoryIds();
            $ignoreNameImage = $this->checkIgnoreNameImage($this->config->getIgnoreImagesCategory(), $url);
            if (($idCategory && $ignoreIdsCategory && in_array($idCategory, $ignoreIdsCategory)) || $ignoreNameImage) {
                return $url;
            }
            return $this->convert->convertUrlFolder($url);
        }
        return $url;
    }

    /**
     * Handle replace url webp in home page
     *
     * @param string $url
     * @param string|int|null $idPage
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException|\Magento\Framework\Exception\NoSuchEntityException
     */
    public function handleCmsPage($url, $idPage = null)
    {
        if ($this->config->isEnableCmsPage()) {
            $id = $idPage ?: $this->request->getParam("page_id");
            $ignoreIds = $this->config->getIgnoreCmsIds();
            $ignoreNameImage = $this->checkIgnoreNameImage($this->config->getIgnoreImagesCms(), $url);
            if (($id && $ignoreIds && in_array($id, $ignoreIds)) || $ignoreNameImage) {
                return $url;
            }
            return $this->convert->convertUrlFolder($url);
        }
        return $url;
    }

    /**
     * Check ignore name ignore
     *
     * @param array $ignoreNameImage
     * @param string $url
     * @return bool
     */
    public function checkIgnoreNameImage($ignoreNameImage, $url)
    {
        if ($ignoreNameImage) {
            foreach ($ignoreNameImage as $image) {
                $image = "/" . $image;
                if (substr($url, -strlen($image)) == $image) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check ignore folders
     *
     * @param string $urlImage
     * @return bool
     */
    public function checkIgnoreFolders($urlImage)
    {
        $lenBase = strlen($this->convert->getBaseDomain($urlImage));
        $folderWeb = substr($urlImage, $lenBase);
        $ignoreFolders = $this->config->getIgnoreFolders();
        if ($ignoreFolders && $folderWeb) {
            foreach ($ignoreFolders as $folder) {
                if (strpos($folderWeb, $folder) === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Replace url image to url image webp
     *
     * @param string $url
     * @return mixed|string
     */
    public function replaceUrl($url)
    {
        try {
            if (substr($url, -strlen(".webp")) === ".webp") {
                return $url;
            }
            if ($this->checkIgnoreFolders($url)) {
                return $url;
            }
            $fullActionName = $this->request->getFullActionName();
            if ($fullActionName == "cms_index_index") {
                return $this->handleHomePage($url);
            }

            if ($fullActionName == "catalog_product_view") {
                return $this->handleProductPage($url);
            }

            if ($fullActionName == "catalog_category_view") {
                return $this->handleCategoryPage($url);
            }

            if ($fullActionName == "cms_page_view") {
                return $this->handleCmsPage($url);
            }
            return $this->convert->convertUrlFolder($url);
        } catch (\Exception $e) {
            $this->convert->writeLog($e->getMessage());
        }
        return $url;
    }
}
