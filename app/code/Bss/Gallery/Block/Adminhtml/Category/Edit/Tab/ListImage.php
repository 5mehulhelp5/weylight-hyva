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
namespace Bss\Gallery\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;

/**
 * Class ListImage
 *
 * @package Bss\Gallery\Block\Adminhtml\Category\Edit\Tab
 */
class ListImage extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Bss\Gallery\Model\CategoryFactory
     */
    protected $bssCategoryFactory;

    /**
     * @var \Bss\Gallery\Model\ItemFactory
     */
    protected $bssItemFactory;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * ListImage constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $collectionFactory
     * @param \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory
     * @param \Bss\Gallery\Model\ItemFactory $bssItemFactory
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $collectionFactory,
        \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory,
        \Bss\Gallery\Model\ItemFactory $bssItemFactory,
        \Magento\Catalog\Model\Session $catalogSession,
        array $data = []
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->bssCategoryFactory = $bssCategoryFactory;
        $this->bssItemFactory = $bssItemFactory;
        $this->catalogSession = $catalogSession;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize the item grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('listImageGrid');
        $this->setSaveParametersInSession(true);
        $this->setDefaultLimit(20);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('id')) {
            $this->setDefaultFilter(['in_images' => 1]);
        }
    }

    /**
     * Get category
     *
     * @return mixed
     */
    public function getCategory()
    {
        return $this->getRequest()->getParam('category_id');
    }

    /**
     * Prepare collection
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create()->addFieldToSelect('*');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add column for filter to collection
     *
     * @param Grid\Column $column
     * @return $this|Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_images') {
            $itemIds = $this->_getSelectedItems();

            if (empty($itemIds)) {
                $itemIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('item_id', ['in' => $itemIds]);
            } else {
                if ($itemIds) {
                    $this->getCollection()->addFieldToFilter('item_id', ['nin' => $itemIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_images',
            [
                'type' => 'checkbox',
                'name' => 'in_images',
                'index' => 'item_id',
                'values' => $this->_getSelectedItems()
            ]
        );
        $this->addColumn(
            'item_id',
            [
                'header' => __('ID'),
                'index' => 'item_id',
            ]
        );
        $this->addColumn(
            'category_thumb',
            [
                'header' => __('Album Thumbnail'),
                'type' => 'radio',
                'html_name' => 'category_thumb',
                'index' => 'item_id',
                'filter' => false,
                'values' => $this->getSelectedThumb()
            ]
        );
        $this->addColumn(
            'item_image',
            [
                'header' => __('Thumbnail'),
                'index' => 'image',
                'renderer' => \Bss\Gallery\Block\Adminhtml\Item\Grid\ImageRenderer::class,
                'filter' => false,
            ]
        );
        $this->addColumn(
            'item_title',
            [
                'header' => __('Title'),
                'index' => 'title',
            ]
        );
        $this->addColumn(
            'item_description',
            [
                'header' => __('Description'),
                'index' => 'description',
            ]
        );
        $this->addColumn(
            'item_is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'renderer' => \Bss\Gallery\Block\Adminhtml\Item\Grid\StatusRenderer::class,
            ]
        );
        $this->addColumn(
            'sorting',
            [
                'header' => __('Order'),
                'name' => 'sorting',
                'index' => 'sorting',
                'width' => '50px',
                'editable' => true,
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'

            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get selected items
     *
     * @return array
     */
    public function getSelectedItems()
    {
        $category_id = $this->getRequest()->getParam('category_id');
        if (!isset($category_id)) {
            return [];
        }
        $category = $this->bssCategoryFactory->create()->load($category_id);
        $itemIds = [];
        if ($category->getData('Item_ids')){
            foreach (explode(',', trim($category->getData('Item_ids'))) as $itemId) {
                $item = $this->getItemById($itemId);
                $itemIds[$itemId] = ['sorting' => $item->getData('sorting')];
            }
        }
        return $itemIds;
    }

    /**
     * Get item by id
     *
     * @param mixed $itemId
     * @return \Bss\Gallery\Model\Item
     */
    protected function getItemById($itemId)
    {
        return $this->bssItemFactory->create()->load($itemId);
    }

    /**
     * Get selected items
     *
     * @return array|mixed
     */
    protected function _getSelectedItems()
    {
        $items = $this->getRequest()->getParam('images');
        if (!is_array($items)) {
            $items = array_keys($this->getSelectedItems());
        }
        return $items;
    }

    /**
     * Get selected thumbnail
     *
     * @return array
     */
    public function getSelectedThumb()
    {
        $category_id = $this->getRequest()->getParam('category_id');
        if (!isset($category_id)) {
            return [];
        }

        $thumb = $this->catalogSession->getCategoryThumb();
        $keys = $this->catalogSession->getKeySession();
        if ($thumb && $keys && $thumb['keys'] == $keys) {
            return [$thumb['id']];
        } else {
            $category = $this->bssCategoryFactory->create()->load($category_id);
            return [$category->getItemThumbId()];
        }
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('gallery/*/listimage', ['_current' => true]);
    }

    /**
     * Get row url
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return '';
    }
}
