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
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2019-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductLabel\Model\Indexer;

use Bss\ProductLabel\Model\ResourceModel\Label\CollectionFactory as LabelCollection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Class ProductIndexer
 *
 * @package Bss\ProductLabel\Model\Indexer
 * @codingStandardsIgnoreFile
 */
class ProductIndexer
{
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var LabelCollection
     */
    protected $labelCollection;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    private $indexer;

    /**
     * Insert records batch size
     * @var int
     */
    private $batchSize = 1000;

    /**
     * @var \Bss\ProductLabel\Helper\ModelLabel
     */
    protected $helperModel;

    /**
     * @var array
     */
    protected $websitesMap;

    /**
     * ProductIndexer constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param LabelCollection $labelCollection
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Indexer\IndexerInterface $indexer
     * @param \Bss\ProductLabel\Helper\ModelLabel $helperModel
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        LabelCollection $labelCollection,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Indexer\IndexerInterface $indexer,
        \Bss\ProductLabel\Helper\ModelLabel $helperModel
    ) {
        $this->localeDate = $localeDate;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->labelCollection = $labelCollection;
        $this->productRepository = $productRepository;
        $this->indexer = $indexer;
        $this->helperModel = $helperModel;
    }

    /**
     * @param int $productId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($productId)
    {
        $this->deleteIndexedProduct($productId);
        $product = $this->productRepository->getById($productId);
        // Clone product to data not set when getById product
        $product = clone $product;
        $newProductLabelData = [];
        $labelCollection = $this->labelCollection->create();

        $connection = $this->getConnection();
        $labelFlatTable = $this->resource->getTableName('bss_productlabel_flat');
        $flagIsPerformInvalidate = false;
        try {
            foreach ($labelCollection as $label) {
                $label->afterLoad();
                $websites = $this->getWebsites($label);
                $allowStore = [];
                foreach ($websites as $storeId) {
                    $this->setSpecialPrice($product);
                    $product->setStoreId($storeId);
                    if ($label->validate($product) && $label->isApplyLabel()) {
                        $allowStore[] = $storeId;
                    }
                }
                if (!empty($allowStore)) {
                    $flagIsPerformInvalidate = true;
                    $newProductLabelData[] = [
                        'label_id' => $label->getId(),
                        'product_id' => $product->getId(),
                        'store_views' => implode(",", $allowStore),
                        'image' => $label->getImage(),
                        'image_data' => $label->getImageData(),
                        'customer_groups' => $label->getCustomerGroups(),
                        'valid_start_date' => $label->getValidStartDate(),
                        'valid_end_date' => $label->getValidEndDate(),
                        'priority' => $label->getPriority()
                    ];

                    if (count($newProductLabelData) == $this->batchSize) {
                        $connection->insertMultiple($labelFlatTable, $newProductLabelData);
                        $newProductLabelData = [];
                    }
                }
            }

            if (!empty($newProductLabelData) &&
                count($newProductLabelData) < $this->batchSize) {
                $connection->insertMultiple($labelFlatTable, $newProductLabelData);
            }

            if ($flagIsPerformInvalidate) {
                $this->invalidateIndexer();
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function getWebsitesMap()
    {
        if (empty($this->websitesMap)) {
            $websites = $this->helperModel->getStoreManager()->getWebsites();
            foreach ($websites as $website) {
                // Continue if website has no store to be able to create catalog rule for website without store
                if ($website->getDefaultStore() === null) {
                    continue;
                }
                $this->websitesMap[$website->getId()] = $website->getDefaultStore()->getId();
            }
        }

        return $this->websitesMap;
    }

    /**
     * @param $label
     * @return array
     */
    protected function getWebsites($label)
    {
        if ($label->getStoreViews()) {
            return explode(",", $label->getStoreViews());
        }
        return $this->getWebsitesMap();
    }

    /**
     * @param int $productId
     */
    protected function deleteIndexedProduct($productId)
    {
        $connection = $this->getConnection();
        $labelFlatTable = $this->resource->getTableName('bss_productlabel_flat');

        $query = $connection->deleteFromSelect(
            $connection->select()
                ->from($labelFlatTable, 'product_id')
                ->where('product_id = ?', $productId),
            $labelFlatTable
        );
        $connection->query($query);
    }

    /**
     * Perform invalidate indexer
     */
    protected function invalidateIndexer()
    {
        // mark indexer as invalidate
        $this->indexer->load(LabelIndexer::INDEXER_ID)->invalidate();
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * Set special price when is not Scope Date Interval special price to validate condition special price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function setSpecialPrice($product)
    {
        $isScopeDateInInterval = $this->localeDate->isScopeDateInInterval(
            WebsiteInterface::ADMIN_CODE,
            $product->getData("special_from_date"),
            $product->getData("special_to_date")
        );
        if (!$isScopeDateInInterval) {
            $product->setData("special_price", null);
        }
    }
}
