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
namespace Bss\Gallery\Model\Item\Source;

/**
 * Class Categories
 *
 * @package Bss\Gallery\Model\Item\Source
 */
class Categories implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Bss\Gallery\Model\Item
     */
    protected $item;

    /**
     * @var \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categories;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Categories constructor.
     *
     * @param \Bss\Gallery\Model\Item $item
     * @param \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $categories
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\Gallery\Model\Item $item,
        \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $categories,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->item = $item;
        $this->request = $request;
        $this->categories = $categories;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $allCategories = $this->categories->create();
        foreach ($allCategories as $cate) {
            $options[] = [
                'label' => $cate->getTitle(),
                'value' => $cate->getId(),
            ];
        }
        return $options;
    }

    /**
     * Get category ids
     *
     * @return array|string[]
     */
    public function getCategoryIds()
    {
        if ($this->request->getParam('item_id')) {
            $categoryIds = $this->item->load($this->request->getParam('item_id'))->getCategoryIds();
            if ($categoryIds) {
                return explode(',', $categoryIds);
            }
        }
        return [];
    }
}
