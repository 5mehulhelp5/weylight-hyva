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
 * @package    Bss_RichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RichSnippets\Plugin;

use DateTime;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

class ProductOffers
{
    /**
     * @var \Bss\RichSnippets\Model\ProductCollection
     */
    protected $productCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository
     */
    protected $productAttributeRepository;

    /**
     * @var \Bss\SeoCore\Helper\Data
     */
    protected $seoCoreHelper;

    /**
     * @var \Bss\RichSnippets\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $pageTitle;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockItemRepository;

    /**
     * @param \Bss\RichSnippets\Helper\Data $helper
     * @param \Magento\Framework\View\Page\Title $pageTitle
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository
     * @param \Bss\RichSnippets\Model\ProductCollection $productCollection
     * @param \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository
     * @param \Bss\SeoCore\Helper\Data $seoCoreHelper
     */
    public function __construct(
        \Bss\RichSnippets\Helper\Data $helper,
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository,
        \Bss\RichSnippets\Model\ProductCollection $productCollection,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Bss\SeoCore\Helper\Data $seoCoreHelper
    ) {
        $this->storeManager = $storeManager;
        $this->stockItemRepository = $stockItemRepository;
        $this->helper = $helper;
        $this->coreRegistry = $registry;
        $this->pageTitle = $pageTitle;
        $this->productCollection = $productCollection;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->seoCoreHelper = $seoCoreHelper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterToHtml($subject, $result)
    {
        $html = '';
        if ($this->getCurrentCategory() && $this->helper->getEnable() && $this->helper->getEnableNameCategory()) {
            $storeId = $this->storeManager->getStore()->getId();
            $productCollection = $subject->getLoadedProductCollection();
            $arrayCollectionWithReview = $this->productCollection->addReviewToProductCollection(
                $subject->getLoadedProductCollection(),
                $storeId
            );
            $dataProduct = $this->getDataProduct($productCollection, $arrayCollectionWithReview);
            $enableDescription = $this->helper->getEnableDescriptionCategory();
            $descriptionCategory = $this->getCurrentCategory()->getDescription();
            $description = $this->getPlainText($descriptionCategory);

            $html = '<script type="application/ld+json">{';
            $html .= '"@context": "http://schema.org/",';
            $html .= '"@type": "WebPage",';
            $html .= '"name": "'.$subject->escapeHtml($this->getTitlePage()).'",';
            if ($enableDescription == '1' && $description != null) {
                $html .= '"description": "'.$subject->escapeHtml($description).'",';
            }
            $html .= '"mainEntity":'.$this->helper->jsonEncode($dataProduct);
            $html .= '}</script>';
        }

        return $result.$html;
    }

    /**
     * Get Plain Text in Description.
     *
     * @param string $descriptionCategory
     * @return string
     */
    public function getPlainText($descriptionCategory)
    {
        if (!$descriptionCategory) {
            return "";
        }

        $desHtmlDecode = htmlspecialchars_decode($descriptionCategory); // encrypted string recovery
        $removeScriptTag = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $desHtmlDecode); // result is HTML deleted tag <script>
        $removeStyleTag = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $removeScriptTag); // result is HTML deleted tag <style>
        $htmlDescription = preg_replace('/{{(.*?)}}/is', '', $removeStyleTag); // result is HTML deleted widget

        $descriptionText = strip_tags($htmlDescription); // remove all HTML tag

        return trim($descriptionText);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }
    /**
     * Get title
     *
     * @return mixed
     */
    public function getTitlePage()
    {
        return $this->pageTitle->getShort();
    }

    /**
     * @param string $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItem($productId)
    {
        return $this->stockItemRepository->getStockItem($productId);
    }

    /**
     * Get helper
     *
     * @return \Bss\RichSnippets\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * Get data product offers
     *
     * @param AbstractCollection $productCollection
     * @param array|null $arrayCollectionWithReview
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD)
     */
    public function getDataProduct($productCollection, $arrayCollectionWithReview)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attributeOffers = [];
        $brandAttribute = $this->helper->getConfig(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_ADD_BRAND);
        if (!empty($brandAttribute)) {
            $attributeOffers[] = $brandAttribute;
        }
        $gtinAttribute = $this->helper->getConfig(\Bss\RichSnippets\Helper\Data::RICH_PRODUCT_GTIN);
        if (!empty($gtinAttribute)) {
            $attributeOffers[] = $gtinAttribute;
        }
        if (!empty($attributeOffers)) {
            $getAttribute = $this->productCollection->getAttribute(
                $productCollection,
                $storeId,
                $attributeOffers
            );
        }

        $itemOffered = [];
        $currentCurrency = $this->getCurrentCurrency();
        $productOffer = $this->getHelper()->isSetFlag(
            \Bss\RichSnippets\Helper\Data::RICH_CATEGORY_PRODUCT_OFFERS
        );
        if ($productCollection->getSize()) {
            foreach ($productCollection as $product) {
                $ratingCount = $product->getReviewsCount();
                $ratingValue = $product->getRatingSummary();
                $stockStatus = $this->getStockItem($product->getId());
                if ($stockStatus->getIsInStock()) {
                    $stockStatusString = 'http://schema.org/InStock';
                } else {
                    $stockStatusString = 'http://schema.org/OutOfStock';
                }

                $description = $product->getMetaDescription();

                if ($description !== null) {
                    $description = strip_tags($description);
                    $description = str_replace('"', '\'', $description);
                } else {
                    $description = '';
                }

                $productImage = $this->helper->getProductImage($product);
                $dataToAdd = [
                    "@type" => "Product",
                    "name" => $product->getName(),
                    "image" => $productImage,
                    "sku" => $product->getSku(),
                    "description" => $description
                ];
                if ($ratingValue) {
                    $dataToAdd = $this->helper->processProductRating($dataToAdd, $ratingValue, $ratingCount);
                }
                // Add Gtin or Mpn
                if ($gtinAttribute && isset($getAttribute[$product->getId()][$gtinAttribute])) {
                    $dataToAdd['gtin'] = $this->getOptionTextValue(
                        $getAttribute[$product->getId()][$gtinAttribute],
                        $gtinAttribute
                    );
                }
                // Add Brand
                if ($brandAttribute && isset($getAttribute[$product->getId()][$brandAttribute])) {
                    $dataToAdd['brand'] = [
                        "@type" => "Brand",
                        "name" => $this->getOptionTextValue(
                            $getAttribute[$product->getId()][$brandAttribute],
                            $brandAttribute
                        )
                    ];
                }
                //$review
                if ($arrayCollectionWithReview && isset($arrayCollectionWithReview[$product->getId()])) {
                    $dataToAdd['review'] = $arrayCollectionWithReview[$product->getId()];
                }

                $specialToDate = $product->getSpecialToDate() ? $product->getSpecialToDate() : '';
                $priceValidUntil = new DateTime($specialToDate);
                if ($productOffer) {
                    $dataToAdd["offers"] = [
                        "@type" => "Offer",
                        "price" => $product->getFinalPrice(),
                        "priceCurrency" => $currentCurrency,
                        "priceValidUntil" => $priceValidUntil->format('Y-m-d'),
                        "url" => $product->getProductUrl(),
                        "availability" => $stockStatusString
                    ];
                }
                if (!isset($dataToAdd["offers"]) && !isset($dataToAdd["aggregateRating"])) {
                    $dataToAdd = [];
                }
                $itemOffered[] = $dataToAdd;
            }
        }
        $dataProduct = [];
        if (!empty($itemOffered)) {
            $dataProduct = [
                "@type" => "WebPageElement",
                "offers" => [
                    "@type" => "Offer",
                    "itemOffered" => $itemOffered
                ]
            ];
        }
        return $dataProduct;
    }

    /**
     * Get option value
     *
     * @param mixed|string $optionValue
     * @param string $optionAttribute
     * @return mixed|string
     */
    protected function getOptionTextValue($optionValue, $optionAttribute)
    {
        try {
            if (is_string($optionValue) && is_string($optionAttribute)) {
                $attr = $this->productAttributeRepository->get($optionAttribute);
                $types = ['boolean', 'select'];
                if (in_array($attr->getFrontendInput(), $types)) {
                    foreach ($attr->getSource()->getAllOptions() as $option) {
                        if ($option['value'] == $optionValue) {
                            return $option['label'];
                        }
                    }
                } elseif ($attr->getFrontendInput() === 'multiselect') {
                    return $this->seoCoreHelper->implode(', ', $attr->getSource()->getOptionText($optionValue));
                }
            }
        } catch (\Exception $exception) {
            return $this->sanitizeOptionValue($optionValue);
        }
        return $this->sanitizeOptionValue($optionValue);
    }

    /**
     * Get option text value
     *
     * @param string|object $optionValue
     * @return string
     */
    protected function sanitizeOptionValue($optionValue)
    {
        if (is_string($optionValue)) {
            return $optionValue;
        }
        if (!$optionValue || is_object($optionValue) || is_array($optionValue)) {
            return '';
        }
        if (is_array($optionValue)) {
            $optionValue = implode(',', $optionValue);
        }
        return (string) $optionValue;
    }
}
