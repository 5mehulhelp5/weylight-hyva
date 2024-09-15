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
namespace Bss\Gallery\Model\Config\Source;

/**
 * Class WidgetCategory
 *
 * @package Bss\Gallery\Model\Config\Source
 */
class WidgetCategory implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * WidgetCategory constructor.
     *
     * @param \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * To option
     *
     * @return array
     */
    public function toOptionArray()
    {
        $categories = $this->collectionFactory->create();
        $categories->addFilter('is_active', 1);
        $array = [];
        foreach ($categories as $cate) {
            $cat = ['value' => $cate->getId(), 'label' => $cate->getTitle()];
            array_push($array, $cat);
        }
        return $array;
    }
}
