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

namespace Bss\ProductLabel\Model;

use Bss\ProductLabel\Model\ResourceModel\Label as ResourceModelLabel;
use Magento\Rule\Model\AbstractModel;

/**
 * Class Label
 * @package Bss\ProductLabel\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 */
class Label extends AbstractModel
{
    /**
     * @var array
     */
    protected $productIds = [];

    /**
     * @var \Bss\ProductLabel\Helper\ModelLabel
     */
    protected $helperModel;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var array
     */
    private $websitesMap = [];

    /**
     * @var array
     */
    private $newProductLabelData = [];

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * Label constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Bss\ProductLabel\Helper\ModelLabel $helperModel
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Indexer\IndexerInterface $indexer
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $abstractResource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context                        $context,
        \Magento\Framework\Registry                             $registry,
        \Magento\Framework\Data\FormFactory                     $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface    $localeDate,
        \Bss\ProductLabel\Helper\ModelLabel                     $helperModel,
        \Magento\Framework\App\ResourceConnection               $resource,
        \Magento\Framework\Indexer\IndexerInterface             $indexer,
        \Magento\Framework\App\Cache\TypeListInterface          $cacheTypeList,
        \Magento\Framework\Serialize\Serializer\Json            $json,
        \Magento\Framework\Model\ResourceModel\AbstractResource $abstractResource = null,
        \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection = null,
        array                                                   $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $abstractResource,
            $resourceCollection,
            $data
        );
        $this->helperModel = $helperModel;
        $this->localeDate = $localeDate;
        $this->resource = $resource;
        $this->indexer = $indexer;
        $this->cacheTypeList = $cacheTypeList;
        $this->json = $json;
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ResourceModelLabel::class);
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    public function getActionsInstance()
    {
        return $this->helperModel->getConditionProduct()->create();
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->helperModel->getCondCombineFactory()->create();
    }

    /**
     * @param null|[] $labelIds
     * @throws \Zend_Db_Statement_Exception
     */
    public function doExecuteFull($labelIds = null)
    {
        $batchSize = $this->helperModel->getBatchSize();
        $connection = $this->resource->getConnection();
        $labelTable = $this->resource->getTableName('bss_productlabel_label');

        $sql = $connection->select()->from(
            $labelTable,
            ['*']
        );

        if (!empty($labelIds)) {
            $sql->where($labelTable . '.id IN (?)', $labelIds);
            $this->deleteIndexDataByLabel($labelIds);
        } else {
            $this->deleteAllIndexedData();
        }

        $query = $connection->query($sql);
        while ($row = $query->fetch()) {
            $this->_resetConditions();
            $this->setData($row);
            if ($this->isApplyLabel()) {
                $this->filterProducts();
            }
            $this->setData([]);
        }

        if (!empty($this->newProductLabelData) &&
            count($this->newProductLabelData) < $batchSize) {
            $connection->insertMultiple($this->getFlatTable(), $this->newProductLabelData);
        }
    }

    /**
     * Filter Product by conditions
     */
    public function filterProducts()
    {
        try {
            $productCollection = $this->helperModel->getProductCollectionFactory()->create();
            //load all custom attribute product to collection

            //add special_from_date and special_to_date -> conditions_serialized with special price
            $this->setSpecialFromDateToDate();
            $productCollection->addAttributeToSelect('*');
            $this->setCollectedAttributes([]);
            $this->getConditions()->collectValidatedAttributes($productCollection);
            $this->helperModel->getIterator()->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->helperModel->getProductFactory()->create()
                ]
            );
        } catch (\Exception $exception) {
            $this->_logger->critical($exception);
        }
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $batchSize = $this->helperModel->getBatchSize();
        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->getWebsitesMap();
        $allowStore = [];

        //check if rule get store exist
        if ($this->getStoreViews()) {
            $websites = explode(",", $this->getStoreViews());
        }

        foreach ($websites as $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if ($this->getConditions()->validate($product)) {
                $startDate = empty($this->getValidStartDate()) ? null : date("Y-m-d H:i", strtotime($this->getValidStartDate()));
                $endDate = empty($this->getValidEndDate()) ? null : date("Y-m-d H:i", strtotime($this->getValidEndDate()));
                $this->newProductLabelData[] = [
                    'label_id' => $this->getId(),
                    'product_id' => $product->getId(),
                    'store_views' => $this->getStoreViews(),
                    'image' => $this->getImage(),
                    'image_data' => $this->getImageData(),
                    'customer_groups' => $this->getCustomerGroups(),
                    'valid_start_date' => $startDate,
                    'valid_end_date' => $endDate,
                    'priority' => $this->getPriority()
                ];

                if (count($this->newProductLabelData) == $batchSize) {
                    $this->getConnection()->insertMultiple($this->getFlatTable(), $this->newProductLabelData);
                    $this->newProductLabelData = [];
                }
            }
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
     * Clean flat table specify label ID
     *
     * @param $labelIds
     * @return $this
     */
    protected function deleteIndexDataByLabel($labelIds)
    {
        $this->getConnection()->delete($this->getFlatTable(), [' label_id IN (?)' => $labelIds]);
        return $this;
    }

    /**
     * Clean flat table
     *
     * @return $this
     */
    protected function deleteAllIndexedData()
    {
        $this->getConnection()->delete($this->getFlatTable());
        return $this;
    }

    /**
     * @param int $labelId
     */
    protected function deleteIndexedLabel($labelId)
    {
        $connection = $this->getConnection();
        $labelFlatTable = $this->getFlatTable();

        $query = $connection->deleteFromSelect(
            $connection->select()
                ->from($labelFlatTable, 'label_id')
                ->where('label_id = ?', $labelId),
            $labelFlatTable
        );
        $connection->query($query);
    }

    /**
     * Check label is allow to apply
     * @return bool
     */
    public function isApplyLabel()
    {
        if (!$this->getActive() ||
            empty($this->getImage()) ||
            empty($this->getImageData()) ||
            !$this->checkValidEndDate($this->getValidEndDate()) ||
            $this->getData('apply_outofstock_product')) {
            return false;
        }

        return true;
    }

    /**
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        $this->deleteIndexedLabel($this->getId());
        return parent::beforeDelete();
    }

    /**
     * @return AbstractModel
      */
    public function afterSave()
    {
        $batchSize = $this->helperModel->getBatchSize();
        // check reindex mode is realtime (Update on Save mode)
        if (!$this->indexer->load(\Bss\ProductLabel\Model\Indexer\LabelIndexer::INDEXER_ID)->isScheduled()) {
            $this->deleteIndexedLabel($this->getId());

            if ($this->isApplyLabel()) {
                $this->filterProducts();
            }

            if (!empty($this->newProductLabelData) &&
                count($this->newProductLabelData) < $batchSize) {
                $connection = $this->resource->getConnection();
                $connection->insertMultiple($this->getFlatTable(), $this->newProductLabelData);
            }

            // mark FPC as invalidate
            $this->cacheTypeList->invalidate(
                \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
            );
        }
        return parent::afterSave();
    }

    /**
     * Set special date special_from_data and special_to_date to condition json
     * @return void
     */
    public function setSpecialFromDateToDate()
    {
        $conditionJsonOld = $this->getData('conditions_serialized');
        $condition = $this->json->unserialize($conditionJsonOld);
        if (isset($condition['conditions'])) {
            $condition['conditions'] = $this->findLocationToSetDate($condition['conditions']);
            $conditionJsonNew = $this->json->serialize($condition);
            $this->setData('conditions_serialized', $conditionJsonNew);
        }
    }

    /**
     * Find location special price using recursive call and replace new conditions all true to this location
     * If conditions has special price attribute -> add (special date from, special price to, special price ) to one conditions all true
     * If conditions hasn't special price -> nothing
     * @param array $condition
     * @return array
     */
    public function findLocationToSetDate($condition)
    {
        foreach ($condition as $key => $value) {
            if ($value['attribute'] === 'special_price') {
                $dataTimeZone = $this->localeDate->date()->format('Y-m-d ');
                $dataTimeZoneMinute = $dataTimeZone . " 00:00:00";
                $currentTime = strtotime($dataTimeZoneMinute);
                $specialToDate = date("Y-m-d H:i:s", $currentTime + 60*60*24 - 1);
                $conditionNew = [
                    "type" => "Magento\CatalogRule\Model\Rule\Condition\Combine",
                    "attribute" => null,
                    "operator" => null,
                    "value" => 1 ,
                    "is_value_processed" => null,
                    "aggregator" => "all",
                    "conditions" => [
                        0 => $value,
                        1 => [
                            "type" => "Magento\CatalogRule\Model\Rule\Condition\Product" ,
                            "attribute" => "special_from_date" ,
                            "operator" => "<=",
                            "value" => $specialToDate,
                            "is_value_processed" => true,
                        ],
                        2 => [
                            "type" => "Magento\CatalogRule\Model\Rule\Condition\Product" ,
                            "attribute" => "special_to_date" ,
                            "operator" => ">=",
                            "value" => $specialToDate,
                            "is_value_processed" => true,
                        ]
                    ]
                ];
                $condition[$key] = $conditionNew;
                return $condition;
            } else {
                if (isset($value['conditions'])) {
                    $condition=$this->findLocationToSetDate($value['conditions']);
                } else {
                    return $condition;
                }
            }
        }
        return $condition;
    }

    /**
     * Check label valid end date
     * @param string $endDate
     * @return bool
     */
    private function checkValidEndDate($endDate)
    {
        if (!empty($endDate)) {
            $dateTimeZone = $this->localeDate->date()->format('Y-m-d H:i:s');
            $currentTime = strtotime($dateTimeZone);
            //check $endDate > $current_time
            if ($currentTime > strtotime($endDate)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @return string
     */
    private function getFlatTable()
    {
        return $this->resource->getTableName('bss_productlabel_flat');
    }
}
