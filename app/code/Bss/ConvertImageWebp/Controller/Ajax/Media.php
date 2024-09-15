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

namespace Bss\ConvertImageWebp\Controller\Ajax;

use Bss\ConvertImageWebp\Model\Config;
use Bss\ConvertImageWebp\Model\Convert;
use Bss\ConvertImageWebp\Model\ReplaceUrl;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Data;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Media
{
    /**
     * @var ProductFactory
     */
    protected $productModelFactory;

    /**
     * @var Data
     */
    protected $swatchHelper;

    /**
     * @var Convert
     */
    protected $convert;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ReplaceUrl
     */
    public $replaceUrl;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var UrlFinderInterface
     */
    protected $urlRewrite;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct.
     *
     * @param ProductFactory $productModelFactory
     * @param Data $swatchHelper
     * @param Convert $convert
     * @param Config $config
     * @param RequestInterface $request
     * @param ReplaceUrl $replaceUrl
     * @param RedirectInterface $redirect
     * @param UrlFinderInterface $urlRewrite
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductFactory $productModelFactory,
        Data $swatchHelper,
        Convert $convert,
        Config $config,
        RequestInterface $request,
        ReplaceUrl $replaceUrl,
        RedirectInterface $redirect,
        UrlFinderInterface $urlRewrite,
        StoreManagerInterface $storeManager
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->swatchHelper = $swatchHelper;
        $this->convert = $convert;
        $this->config = $config;
        $this->request = $request;
        $this->replaceUrl = $replaceUrl;
        $this->redirect = $redirect;
        $this->urlRewrite = $urlRewrite;
        $this->storeManager = $storeManager;
    }

    /**
     * Before Execute Ajax Controller
     *
     * @param \Magento\Swatches\Controller\Ajax\Media $subject
     * @return null
     */
    public function beforeExecute(\Magento\Swatches\Controller\Ajax\Media $subject)
    {
        try {
            if (!$this->config->isEnableModule() || !$this->config->getAutoGenerateWebpImage()) {
                return null;
            }

            $qualityWebp = $this->convert->getQualityImageWebp();
            if ($productId = (int)$subject->getRequest()->getParam('product_id')) {
                /** @var ProductInterface $product */
                $product = $this->productModelFactory->create()->load($productId);
                if ($product->getId() && $product->getStatus() == Status::STATUS_ENABLED) {
                    $productMedia = $this->swatchHelper->getProductMediaGallery($product);

                    $imgUriSmall = $this->convert->getSourceImageUri($productMedia['small']);
                    if ($this->convert->checkExistsFile($imgUriSmall)) {
                        $this->convert->convert(
                            $imgUriSmall,
                            $qualityWebp,
                        );
                    }

                    $imgUriMedium = $this->convert->getSourceImageUri($productMedia['medium']);
                    if ($this->convert->checkExistsFile($imgUriMedium)) {
                        $this->convert->convert(
                            $imgUriMedium,
                            $qualityWebp
                        );
                    }

                    $imgUriLarge = $this->convert->getSourceImageUri($productMedia['large']);
                    if ($this->convert->checkExistsFile($imgUriLarge)) {
                        $this->convert->convert(
                            $imgUriLarge,
                            $qualityWebp
                        );
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * AfterExecute function ajax controller
     *
     * @param \Magento\Swatches\Controller\Ajax\Media $subject
     * @param array|mixed $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(\Magento\Swatches\Controller\Ajax\Media $subject, $result)
    {
        try {
            if (!$this->config->isEnableModule()) {
                return $result;
            }

            $productMedia = [];
            if ($productId = (int)$subject->getRequest()->getParam('product_id')) {
                /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                $product = $this->productModelFactory->create()->load($productId);
                if ($product->getId() && $product->getStatus() == Status::STATUS_ENABLED) {
                    $productMedia = $this->swatchHelper->getProductMediaGallery($product);
                    if ($this->canConvertImg($productMedia['small'])) {
                        $imgUriSmall = $this->convert->getSourceImageUri($productMedia['small']);
                        if (!$this->convert->checkExistsFile($imgUriSmall)) { // not exist img cache
                            $productMedia['small'] = $this->convert->replaceWebpConvertAutoFe($imgUriSmall);
                        } else {
                            $productMedia['small'] = $this->convert->convertUrlFolder($productMedia['small']);
                        }
                    }

                    if ($this->canConvertImg($productMedia['medium'])) {
                        $imgUriMedium = $this->convert->getSourceImageUri($productMedia['medium']);
                        if (!$this->convert->checkExistsFile($imgUriMedium)) { // not exist img cache
                            $productMedia['medium'] = $this->convert->replaceWebpConvertAutoFe($imgUriMedium);
                        } else {
                            $productMedia['medium'] = $this->convert->convertUrlFolder($productMedia['medium']);
                        }
                    }

                    if ($this->canConvertImg($productMedia['large'])) {
                        $imgUriLarge = $this->convert->getSourceImageUri($productMedia['large']);
                        if (!$this->convert->checkExistsFile($imgUriLarge)) { // not exist img cache
                            $productMedia['large'] = $this->convert->replaceWebpConvertAutoFe($imgUriLarge);
                        } else {
                            $productMedia['large'] = $this->convert->convertUrlFolder($productMedia['large']);
                        }
                    }
                }
            }
            $result->setData($productMedia);
            return $result;
        } catch (\Exception $e) {
            return $result;
        }
    }

    /**
     * Check enable for specific page
     *
     * @param string $urlImg
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canConvertImg($urlImg)
    {
        $redirectUrl = $this->redirect->getRedirectUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $requestPath = str_replace($baseUrl, '', $redirectUrl);
        $requestPath = strstr($requestPath, '?', true) ?: $requestPath;
        $url = $this->urlRewrite->findOneByData([
            UrlRewrite::REQUEST_PATH => $requestPath
        ]);
        $page = $url ? $url->getEntityType() : '';

        if ($page) {
            if ($page === 'category') {
                $imgWebp = $this->replaceUrl->handleCategoryPage($urlImg, $url->getEntityId());
            } elseif ($page === 'cms-page') {
                $imgWebp = $this->replaceUrl->handleCmsPage($urlImg, $url->getEntityId());
            } else {
                $imgWebp = $this->replaceUrl->handleProductPage($urlImg);
            }
        } else {
            $imgWebp = $this->replaceUrl->handleHomePage($urlImg);
        }

        if (strpos($imgWebp, '.webp') !== false) { // image not ignore
            return true;
        } else { // image ignore
            return false;
        }
    }
}
