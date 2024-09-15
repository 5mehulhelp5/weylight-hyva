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
 * @package    Bss_Gallery
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Model\ResourceModel;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class CategoryImport
 *
 * @package Bss\Gallery\Model\ResourceModel
 */
class CategoryImport
{
    /**
     * @var int
     */
    protected $insertedRows = 0;

    /**
     * @var string
     */
    protected $wrongTitleRows = "";

    /**
     * @var int
     */
    protected $invalidDataRows = 0;

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory
     */
    protected $sourceCsvFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\Gallery\Helper\Data
     */
    protected $dataHelper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * CategoryImport constructor.
     *
     * @param \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\Gallery\Helper\Data $dataHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\Gallery\Helper\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        /** @var TYPE_NAME $sourceCsvFactory */
        $this->sourceCsvFactory = $sourceCsvFactory;
        $this->filesystem = $filesystem;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
        $this->writeAdapter = $this->resourceConnection->getConnection('core_write');
        $this->logger = $logger;
    }

    /**
     * Get table name
     *
     * @param int $entity
     * @return bool|string
     */
    protected function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * Import from csv file
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile()
    {
        $sourceCsv = $this->createSourceCsvModel($this->getFilePath());
        $sourceCsv->rewind();
        $numRow = 0;
        while ($sourceCsv->valid()) {
            $numRow++;
            $data = $sourceCsv->current();
            if ($this->validation($numRow, $data) === false) {
                $this->invalidDataRows++;
            } else {
                $this->processData($data);
                $this->insertedRows++;
            }
            $sourceCsv->next();
        }
    }

    /**
     * Validation
     *
     * @param int $rowNum
     * @param array $rowData
     * @return bool
     */
    protected function validation($rowNum, $rowData)
    {
        if ($rowData['Album Title'] == "") {
            $this->wrongTitleRows .= "$rowNum, ";
            return false;
        }
        return true;
    }

    /**
     * Process data
     *
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processData($data)
    {
        $importData = $this->prepareData($data);

        try {
            if ($this->checkExistedCategoryId($data['Album Id']) === false) {
                $dataKey = $this->getUrlKey($importData['title']);
                $importData['url_key'] = $dataKey['url_key'];
                $this->writeAdapter->insert($this->getTableName('bss_gallery_category'), $importData);
                $categoryId = (int)$dataKey['url_key'] + 1;
            } else {
                unset($importData['create_time']);
                $existedId = $this->checkExistedCategoryId($data['Album Id']);
                $condition = ["{$this->getTableName('bss_gallery_category')}.category_id = ?" => $existedId];
                $this->writeAdapter->update($this->getTableName('bss_gallery_category'), $importData, $condition);
                $categoryId = $data['Album Id'];
            }

            if ($importData['Item_ids']){
                $itemIds = explode(",", $importData['Item_ids']);
                foreach ($itemIds as $itemId) {
                    $categoryIds = explode(",", $this->getCategoryFromItem($itemId) ?? '');
                    if (!in_array($categoryId, $categoryIds)) {
                        $updateCategoryIds['category_ids'] = $this->getCategoryFromItem($itemId) . "," . $categoryId;

                        $condition = ["{$this->getTableName('bss_gallery_item')}.item_id = ?" => $itemId];
                        $this->writeAdapter->update(
                            $this->getTableName('bss_gallery_item'),
                            $updateCategoryIds,
                            $condition
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Get category id from item
     *
     * @param int $itemId
     * @return string
     */
    protected function getCategoryFromItem($itemId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('bss_gallery_item'),
                [
                    'category_ids'
                ]
            )
            ->where('item_id = :item_id');
        $bind = [
            ':item_id' => $itemId
        ];
        $categoryIds = $this->readAdapter->fetchOne($select, $bind);
        return $categoryIds;
    }

    /**
     * Prepare data for import
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        $importData = [];
        $importData['title'] = $data['Album Title'];
        $importData['category_description'] = $data['Album Description'];
        $importData['category_meta_keywords'] = $data['Meta Key'];
        $importData['category_meta_description'] = $data['Meta Description'];

        if ($data['Layout'] === '1' || $data['Layout'] === '2') {
            $importData['item_layout'] = $data['Layout'];
        } else {
            $importData['item_layout'] = 1;
        }

        if ($data['Auto Play'] === '0' || $data['Auto Play'] === '1') {
            $importData['slider_auto_play'] = $data['Auto Play'];
        } else {
            $importData['slider_auto_play'] = 1;
        }

        if ($data['Status'] === '0' || $data['Status'] === '1') {
            $importData['is_active'] = $data['Status'];
        } else {
            $importData['is_active'] = 1;
        }

        $importData['Item_ids'] = $data['Item Ids'];
        $importData['create_time'] = date('Y-m-d H:i:s');
        $importData['update_time'] = date('Y-m-d H:i:s');
        return $importData;
    }

    /**
     * Get url key
     *
     * @param string $title
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getUrlKey($title)
    {
        $urlKey = $this->dataHelper->formatUrlKey($title);
        $lastCategoryId = $this->getLastCategoryId();
        if ($this->checkExistedUrlKey($urlKey) !== false) {
            $urlKey .= "-" . $this->dataHelper->randomStr();
        }
        return ['url_key' => $urlKey, 'id' => $lastCategoryId];
    }

    /**
     * Check if url key is exist
     *
     * @param string $urlKey
     * @return string
     */
    protected function checkExistedUrlKey($urlKey)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('bss_gallery_category'),
                [
                    'category_id'
                ]
            )
            ->where('url_key = :url_key');
        $bind = [
            ':url_key' => $urlKey
        ];
        $categoryId = $this->readAdapter->fetchOne($select, $bind);
        return $categoryId;
    }

    /**
     * Check if category id is exist
     *
     * @param int $categoryId
     * @return string
     */
    protected function checkExistedCategoryId($categoryId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('bss_gallery_category'),
                [
                    'category_id'
                ]
            )
            ->where('category_id = :category_id');
        $bind = [
            ':category_id' => $categoryId
        ];
        $categoryId = $this->readAdapter->fetchOne($select, $bind);
        return $categoryId;
    }

    /**
     * Get last of category id
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getLastCategoryId()
    {
        $entityStatus = $this->readAdapter->showTableStatus($this->getTableName('bss_gallery_category'));

        if (empty($entityStatus['Auto_increment'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot get autoincrement value'));
        }
        return $entityStatus['Auto_increment'];
    }

    /**
     * Create csv source
     *
     * @param string $sourceFile
     * @return \Magento\ImportExport\Model\Import\Source\Csv
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function createSourceCsvModel($sourceFile)
    {
        return $this->sourceCsvFactory->create(
            [
                'file' => $sourceFile,
                'directory' => $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ]
        );
    }

    /**
     * Set file path
     *
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Get inserted rows
     *
     * @return int
     */
    public function getInsertedRows()
    {
        return $this->insertedRows;
    }

    /**
     * Get wrong title rows
     *
     * @return int
     */
    public function getWrongTitleRows()
    {
        return rtrim($this->wrongTitleRows, ",");
    }

    /**
     * Get invalid rows
     *
     * @return int
     */
    public function getInvalidRows()
    {
        return $this->invalidDataRows;
    }
}
