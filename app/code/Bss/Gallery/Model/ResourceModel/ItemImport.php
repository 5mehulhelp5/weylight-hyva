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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Model\ResourceModel;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ItemImport
 *
 * @package Bss\Gallery\Model\ResourceModel
 */
class ItemImport
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
     * @var string
     */
    protected $wrongDescriptionRows = "";

    /**
     * @var string
     */
    protected $wrongImagePathRows = "";

    /**
     * @var string
     */
    protected $wrongSortOrderRows = "";

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ItemImport constructor.
     * @param \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->sourceCsvFactory = $sourceCsvFactory;
        $this->filesystem = $filesystem;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
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
     * Import from csv
     *
     * @throws \Magento\Framework\Exception\FileSystemException
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
        if ($rowData['Item Name'] == "") {
            $this->wrongTitleRows .= "$rowNum, ";
            return false;
        }

        if ($rowData['Item Description'] == "") {
            $this->wrongDescriptionRows .= "$rowNum, ";
            return false;
        }

        if (!$this->isValidSortOrder($rowData)) {
            $this->wrongSortOrderRows .= "$rowNum, ";
            return false;
        }

        $check = substr($rowData['Image Path'], strrpos($rowData['Image Path'], '.'));
        $allowType = ['.jpg', '.jpeg', '.png', '.bmp', '.gif'];
        if (!in_array($check, $allowType)) {
            $this->wrongImagePathRows .= "$rowNum, ";
            return false;
        }
        return true;
    }

    /**
     * @param $rowData
     * @return bool
     */
    protected function isValidSortOrder($rowData)
    {
        if (isset($rowData['Sort Order']) && ($rowData['Sort Order'] == "" || (int)$rowData['Sort Order'] < 0)) {
            return false;
        }
        return true;
    }

    /**
     * Process data
     *
     * @param array $data
     */
    protected function processData($data)
    {
        $importData = $this->prepareData($data);

        try {
            if ($this->checkExistedItemId($data['Item Id']) === false) {
                $importData['item_id'] = $data['Item Id'];
                $this->writeAdapter->insert($this->getTableName('bss_gallery_item'), $importData);
                $itemId = $this->getLastItemId();
            } else {
                unset($importData['create_time']);
                $existId = $this->checkExistedItemId($data['Item Id']);
                $condition = ["{$this->getTableName('bss_gallery_item')}.item_id = ?" => $existId];
                $this->writeAdapter->update($this->getTableName('bss_gallery_item'), $importData, $condition);
                $itemId = $data['Item Id'];
            }

            if ($importData['category_ids']){
                $categoryIds = explode(",", $importData['category_ids']);
                foreach ($categoryIds as $categoryId) {
                    if ($this->getItemsFromCategory($categoryId)) {
                        $itemIds = explode(",", $this->getItemsFromCategory($categoryId));
                        if (!in_array($itemId, $itemIds)) {
                            $updateData['Item_ids'] = $this->getItemsFromCategory($categoryId) . "," . $itemId;
                            $condition = ["{$this->getTableName('bss_gallery_category')}.category_id = ?" => $categoryId];
                            $this->writeAdapter->update($this->getTableName('bss_gallery_category'), $updateData, $condition);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Prepare item for import
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        $importData = [];
        $importData['title'] = $data['Item Name'];
        $importData['description'] = $data['Item Description'];
        $importData['image'] = $data['Image Path'];
        $importData['video'] = $data['Video Url'];
        $importData['sorting'] = $data['Sort Order'];

        if ($data['Status'] === '0' || $data['Status'] === '1') {
            $importData['is_active'] = $data['Status'];
        } else {
            $importData['is_active'] = 1;
        }
        $importData['category_ids'] = $data['Album Ids'];
        $importData['create_time'] = date('Y-m-d H:i:s');
        $importData['update_time'] = date('Y-m-d H:i:s');
        return $importData;
    }

    /**
     * Check if item is exist
     *
     * @param int $itemId
     * @return string
     */
    protected function checkExistedItemId($itemId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('bss_gallery_item'),
                [
                    'item_id'
                ]
            )
            ->where('item_id = :item_id');
        $bind = [
            ':item_id' => $itemId
        ];
        $itemId = $this->readAdapter->fetchOne($select, $bind);
        return $itemId;
    }

    /**
     * Get last item id
     *
     * @return string
     */
    protected function getLastItemId()
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('bss_gallery_item')],
                ['item_id']
            )
            ->order('item_id DESC')
            ->limit(1);
        $maxId = $this->readAdapter->fetchOne($select);
        return $maxId;
    }

    /**
     * Get items from category
     *
     * @param int $categoryId
     * @return string
     */
    protected function getItemsFromCategory($categoryId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('bss_gallery_category'),
                [
                    'Item_ids'
                ]
            )
            ->where('category_id = :category_id');
        $bind = [
            ':category_id' => $categoryId
        ];
        $itemIds = $this->readAdapter->fetchOne($select, $bind);
        return $itemIds;
    }

    /**
     * Create source csv file
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
     * Get successfully inserted rows
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
        return $this->wrongTitleRows;
    }

    /**
     * Get wrong description rows
     *
     * @return string
     */
    public function getWrongDescriptionRows()
    {
        return $this->wrongDescriptionRows;
    }

    /**
     * Get wrong description rows
     *
     * @return string
     */
    public function getWrongSortOrderRows()
    {
        return $this->wrongSortOrderRows;
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

    /**
     * Get wrong image path rows
     *
     * @return string
     */
    public function getWrongImagePathRows()
    {
        return $this->wrongImagePathRows;
    }
}
