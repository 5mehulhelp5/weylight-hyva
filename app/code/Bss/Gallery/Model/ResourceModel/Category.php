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

use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * Class Category
 *
 * @package Bss\Gallery\Model\ResourceModel
 */
class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Bss\Gallery\Model\ItemFactory
     */
    private $itemFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Category constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Bss\Gallery\Model\ItemFactory $itemFactory
     * @param LoggerInterface $logger
     * @param string $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Bss\Gallery\Model\ItemFactory $itemFactory,
        LoggerInterface $logger,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->date = $date;
        $this->itemFactory = $itemFactory;
        $this->logger = $logger;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_gallery_category', 'category_id');
    }

    /**
     * Before save cate
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $itemCollection = $this->itemFactory->create()->getCollection()->getItems();
        $itemIds = $object->getData('Item_ids');
        if ($itemIds){
            $itemList = explode(',', $itemIds);
            foreach ($itemList as $itemId) {
                if (!array_key_exists($itemId, $itemCollection)) {
                    unset($itemList[array_search($itemId, $itemList)]);
                }
            }
            $object->setData('Item_ids', implode(',', $itemList));
        }



        if ($object->isObjectNew() && !$object->hasCreateTime()) {
            $object->setCreateTime($this->date->gmtDate());
        }
        $object->setUpdateTime($this->date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Before delete cate
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $itemModel = $this->itemFactory->create();
        if ($object->getData('Item_ids')){
            foreach (explode(',', $object->getData('Item_ids')) as $itemId) {
                $item = $this->loadItemId($itemModel, $itemId);
                if ($item->getCategoryIds()){
                    $itemCateList = explode(',', $item->getCategoryIds());
                    if (array_search($object->getCategoryId(), $itemCateList) !== false) {
                        unset($itemCateList[array_search($object->getCategoryId(), $itemCateList)]);
                        $item->setCategoryIds(implode(',', $itemCateList));
                        $this->saveItem($item);
                    }
                }
            }
        }
        return parent::_beforeDelete($object);
    }

    /**
     * Load id of item
     *
     * @param \Bss\Gallery\Model\Item $itemModel
     * @param int $itemId
     * @return \Bss\Gallery\Model\Item
     */
    private function loadItemId($itemModel, $itemId)
    {
        return $itemModel->load($itemId);
    }

    /**
     * Save the cate
     *
     * @param \Bss\Gallery\Model\Item $item
     * @return \Bss\Gallery\Model\Item
     * @throws \Exception
     */
    private function saveItem($item)
    {
        try {
            return $item->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this;
        }
    }

    /**
     * Load an object using 'category_id' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $value
     * @param string $field
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'url_key';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Bss\Gallery\Model\Category $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->where(
                'is_active = ?',
                1
            )->limit(
                1
            );
        }

        return $select;
    }

    /**
     * Load by url key
     *
     * @param string $urlKey
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getLoadByUrlKeySelect($urlKey, $isActive = null, $id = null)
    {
        $select = $this->getConnection()->select()->from(
            ['bp' => $this->getMainTable()]
        )->where(
            'bp.url_key = ?',
            $urlKey
        );

        if ($id !== null) {
            $select->where('bp.category_id != ?', $id);
        }
        if ($isActive !== null) {
            $select->where('bp.is_active = ?', $isActive);
        }
        return $select;
    }

    /**
     *  Check whether category url key is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericCategoryUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('url_key'));
    }

    /**
     *  Check whether post url key is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidCategoryUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('url_key'));
    }

    /**
     * Check url key
     *
     * @param string $urlKey
     * @param int $id
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkUrlKey($urlKey, $id)
    {
        $select = $this->getLoadByUrlKeySelect($urlKey, 1, $id);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS)->columns('bp.category_id')->limit(1);
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get id of last item
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastCategoryId()
    {
        $connection = $this->getConnection();
        $entityStatus = $connection->showTableStatus($connection->getTableName('bss_gallery_category'));

        if (empty($entityStatus['Auto_increment'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot get autoincrement value'));
        }
        return $entityStatus['Auto_increment'];
    }
}
