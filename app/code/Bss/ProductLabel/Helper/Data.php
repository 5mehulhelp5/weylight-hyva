<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Helper;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Data
 * @package Bss\ProductLabel\Helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\ProductLabel\Model\LabelFactory
     */
    protected $labelFatory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Bss\ProductLabel\Model\ResourceModel\Label
     */
    protected $labelResource;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Bss\ProductLabel\Model\LabelFactory $labelFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Bss\ProductLabel\Model\ResourceModel\Label $labelResource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Bss\ProductLabel\Model\LabelFactory $labelFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Bss\ProductLabel\Model\ResourceModel\Label $labelResource
    ) {
        $this->labelFatory = $labelFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->timezone = $timezone;
        $this->stockRegistry = $stockRegistry;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->json = $json;
        $this->labelResource = $labelResource;
        parent::__construct($context);
    }

    /**
     * Check module is enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'productlabel/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is display multiple label
     *
     * @param int $storeId
     * @return bool
     */
    public function isDisplayMultipleLabel($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'productlabel/general/display_multiple_label',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is only display out of stock label
     *
     * @param int $storeId
     * @return bool
     */
    public function isEnableOnlyOutOfStockLabel($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'productlabel/general/display_only_out_of_stock_label',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Label is not display on
     *
     * @param int $storeId
     * @return string
     */
    public function isNotDisplayOn($storeId = null)
    {
        $display = $this->scopeConfig->getValue(
            'productlabel/general/not_display_label_on',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $display;
    }

    /**
     * Check page is allow in system config
     *
     * @param \Bss\ProductLabel\Model\Config\Source\PageDisplayLabel $page
     * @return bool
     */
    public function isSystemConfigAllow($page)
    {
        $pos = false;
        if (!empty($this->isNotDisplayOn())) {
            $pos = strpos($this->isNotDisplayOn(), $page);
        }
        return (($this->isEnable() == true) && ($pos === false));
    }

    /**
     * Get the batch size
     *
     * @param int $storeId
     * @return int
     */
    public function getBatchSize($storeId = null)
    {
        $batchSize = $this->scopeConfig->getValue(
            'productlabel/general/batchsize',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $batchSize;
    }

    /**
     * Get selector product list
     *
     * @param int $storeId
     * @return string
     */
    public function getSelectorProductList($storeId = null)
    {
        $selector = $this->scopeConfig->getValue(
            'productlabel/display/product_list',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $selector;
    }

    /**
     * Get selector product page
     *
     * @param int $storeId
     * @return string
     */
    public function getSelectorProductPage($storeId = null)
    {
        $selector = $this->scopeConfig->getValue(
            'productlabel/display/product_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $selector;
    }

    /**
     * Get media url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManagerInterface->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        return $mediaUrl;
    }

    /**
     * Get label data by product attribute 'label_data'
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $storeView
     * @param int $customerGroupId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLabelData($product, $storeView = null, $customerGroupId = null)
    {
        $labelData = [];

        // Check product is out of stock
        $outStockLabel = $this->getOutOfStockLabelData($product, $storeView, $customerGroupId);

        if (!empty($outStockLabel)) {
            if ($this->isEnableOnlyOutOfStockLabel()) {
                $labelImageData = $this->json->unserialize($outStockLabel['image_data'], true);
                $labelImageData['image'] = $this->getMediaUrl() . $outStockLabel['image'];
                // avoid case of z-index is zero or smaller than product image's
                $labelImageData['priority'] = $outStockLabel['priority'] + 10;
                $labelImageData['product_id'] = $product->getId();
                $labelImageData['name'] = $outStockLabel['name'];
                $labelImageData['image_data'] = $this->json->unserialize($outStockLabel['image_data']);
                $labelImageData['apply_outofstock_product'] = $outStockLabel['apply_outofstock_product'];
                return [$labelImageData];
            }
            $labelData[] = $outStockLabel;
        }

        $labelData = $this->getLabelDataProduct($product, $labelData);

        if (!empty($labelData)) {
            $labelData = $this->filterLabelData($labelData, $product->getId(), $storeView, $customerGroupId);
            usort($labelData, [$this, 'customUsort']);
            if (!$this->isDisplayMultipleLabel()) {
                return empty($labelData) ? [] : [$labelData[0]];
            }
        }

        return $labelData;
    }

    /**
     * Get Out of stock product label
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $storeView
     * @param int $customerGroupId
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getOutOfStockLabelData($product, $storeView = null, $customerGroupId = null)
    {
        if ($storeView == null) {
            $storeView = $this->storeManagerInterface->getStore()->getId();
        }
        if ($customerGroupId == null) {
            $customerGroupId = $this->customerSessionFactory->create()->getCustomerGroupId();
        }
        $outStockLabel = [];
        if ($product->getIsSalable() == 0) {
            $dateTimeZone = $this->timezone->date()->format('Y-m-d H:i:s');
            $outStockLabel = $this->labelFatory->create()->getCollection()
                ->addFieldToFilter('active', true)
                ->addFieldToFilter('apply_outofstock_product', true)
                ->addFieldToFilter('image', ['notnull' => true ])
                ->addFieldToFilter('image_data', ['notnull' => true ])
                ->addFieldToFilter('valid_start_date', [['lt' => $dateTimeZone], ['null' => true]])
                ->addFieldToFilter('valid_end_date', [['gt' => $dateTimeZone], ['null' => true ]])
                ->addFieldToFilter(
                    'store_views',
                    [
                        ['finset' => [$storeView]],
                        ['null' => true ]
                    ]
                )
                ->addFieldToFilter(
                    'customer_groups',
                    [
                        ['finset' => [$customerGroupId]],
                        ['null' => true ]
                    ]
                )
                ->setOrder('priority', 'DESC')
                ->getData();
            $outStockLabel = current($outStockLabel);
            return $outStockLabel;
        }
        return $outStockLabel;
    }

    /**
     * Get Product Label data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $labelData
     * @return array
     */
    protected function getLabelDataProduct($product, $outStockLabelData)
    {
        $newLabelData = $this->labelResource->getIndexedProduct($product->getId());

        // Get label by currently store view
        // Ignore all duplicate label.
        // Reason: If website has multiple store, one product will be assigned to one label for each store
        // That causing duplicate label at front end
        $currentStoreView = $this->storeManagerInterface->getStore()->getId();
        $newLabelDataArr = [];
        foreach ($newLabelData as $labelDatum) {
            $storeIdsAssigned = [];
            if (isset($labelDatum['store_views']) && $labelDatum['store_views'] && is_string($labelDatum['store_views'])) {
                $storeIdsAssigned = explode(',', $labelDatum['store_views']);
            }
            if (!isset($newLabelDataArr[$labelDatum['label_id']])) {
                if (empty($storeIdsAssigned) || (in_array($currentStoreView, $storeIdsAssigned))) {
                    $newLabelDataArr[$labelDatum['label_id']] = $labelDatum;
                }
            }
        }
        // End

        if (!empty($newLabelDataArr)) {
            if (!empty($outStockLabelData)) {
                // check duplicate between "out of stock label" and "product's label existed"
                if (isset($outStockLabelData[0]) && isset($outStockLabelData[0]['id'])) {
                    $key = array_search($outStockLabelData[0]['id'], array_column($newLabelDataArr, 'label_id'));
                    if (is_int($key) != false) {
                        $outStockLabelData = [];
                    }
                }
            }
            $outStockLabelData = array_merge($outStockLabelData, $newLabelDataArr);
        }

        return $outStockLabelData;
    }

    /**
     * Label data filter
     *
     * @param array $labelDatas
     * @param int $productId
     * @param int $storeView
     * @param int $customerGroupId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function filterLabelData($labelDatas, $productId, $storeView = null, $customerGroupId = null)
    {
        try {
            $data = [];
            if (!empty($labelDatas)) {
                foreach ($labelDatas as $labelData) {
                    if (empty($labelData)) {
                        continue;
                    }
                    // Check customer group/store view/date is valid
                    if ($this->isCustomerGroupValid($labelData['customer_groups'], $customerGroupId)
                        && $this->checkValidDate($labelData['valid_start_date'], $labelData['valid_end_date'])
                        && $this->isStoreViewValid($labelData['store_views'], $storeView)) {
                        $labelImageData = $this->json->unserialize($labelData['image_data']);
                        $labelImageData['image'] = $this->getMediaUrl() . $labelData['image'];
                        // avoid case of z-index is zero or smaller than product image's
                        $labelImageData['priority'] = $labelData['priority'] + 10;
                        $labelImageData['product_id'] = $productId;
                        $labelImageData['label_id'] = $labelData['id'] ?? $labelData['label_id'];
                        $label = $this->labelFatory->create()->load($labelImageData["label_id"]);
                        $labelImageData['name'] = $label->getName();
                        $labelImageData['image_data'] = $this->json->unserialize($labelData['image_data']);
                        $data[] = $labelImageData;
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->_logger->critical($exception);
        }

        return $data;
    }

    /**
     * Sort by priority
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function customUsort($a, $b)
    {
        if ($a['priority'] == $b['priority']) {
            return 0;
        }
        return ($a['priority'] < $b['priority']) ? 1 : -1;
    }

    /**
     * Check label valid date
     *
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function checkValidDate($startDate, $endDate)
    {
        $dateTimeZone = $this->timezone->date()->format('Y-m-d H:i:s');
        $currentTime = strtotime($dateTimeZone);

        // checking Label is not yet display
        if (!empty($startDate) && $currentTime < strtotime($startDate)) {
            return false;
        }

        // checking Label is expired
        if (!empty($endDate) && $currentTime > strtotime($endDate)) {
            return false;
        }

        return true;
    }

    /**
     * Check customer group is valid
     *
     * @param $inputGroups
     * @return bool
     */
    public function isCustomerGroupValid($inputGroups, $customerGroupId = null)
    {
        if ($inputGroups != null) {
            $inputGroupsArr = explode(',', $inputGroups);
            if ($customerGroupId == null) {
                $customerGroupId = $this->customerSessionFactory->create()->getCustomerGroupId();
            }
            return in_array($customerGroupId, $inputGroupsArr);
        }
        return true;
    }

    /**
     * Check store view is valid
     *
     * @param string $inputStore
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isStoreViewValid($inputStore, $currentStoreId = null)
    {
        if (!empty($inputStore)) {
            $inputGroupsArr = explode(',', $inputStore);
            if ($currentStoreId == null) {
                $currentStore = $this->storeManagerInterface->getStore();
                $currentStoreId = $currentStore->getId();
            }
            return in_array($currentStoreId, $inputGroupsArr);
        }
        return true;
    }

    /**
     * Get stock item
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($product)
    {
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        return $stockItem;
    }

    /**
     * Get product label by id
     *
     * @param int $id
     * @return mixed|null
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getProductLabelById($id)
    {
        try {
            $label = $this->labelFatory->create()->load($id);
            $data = $label->getData();
        } catch (Exception $exception) {
            throw new Exception(__($exception->getMessage()));
        }
        if (isset($data["conditions_serialized"]) && !empty($data["conditions_serialized"])) {
            $data["file"] = $this->
                getMediaUrl() . $data['image'];
            $data["image_data"] = $this->json->unserialize($data["image_data"]);
            $data["conditions_serialized"] = $this->json->unserialize($data["conditions_serialized"]);
            if (isset($data["conditions_serialized"]["conditions"])) {
                $data["conditions_serialized"]["conditions"] =
                    $this->json->serialize($data["conditions_serialized"]["conditions"]);
            }
        }
        return $data;
    }
}
