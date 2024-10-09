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
 * @copyright  Copyright (c) 2021-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\RichSnippets\Model;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Escaper;

class ProductCollection
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var \Bss\RichSnippets\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param Escaper $escaper
     * @param \Bss\RichSnippets\Helper\Data $helper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Escaper $escaper,
        \Bss\RichSnippets\Helper\Data $helper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Prepare all data review for product collection
     *
     * @param AbstractCollection $collection
     * @param int $storeId
     * @return array|null
     */
    public function addReviewToProductCollection($collection, $storeId)
    {
        if (count($collection->getAllIds()) > 0) {
            $getAllCollectionIds = implode(',', $collection->getAllIds());
            $collections = $this->getReviewProduct($getAllCollectionIds, $storeId);
            if (count($collections)) {
                $allDataReview = [];
                foreach ($collections as $item) {
                    $entityId = $item['entity_id'];
                    $dataReview = [
                        "@type" => "Review",
                        "author" => ["@type" => "Person","name" => $this->escaper->escapeHtml($item['nickname'])],
                        "datePublished" => $this->escaper->escapeHtml($item['created_at']),
                        "description" => $this->escaper->escapeHtml($item['detail'])
                    ];
                    $allDataReview[$entityId][] = $dataReview;
                }
                return $allDataReview;
            }
        }
        return null;
    }

    /**
     * Get review product
     *
     * @param string $getAllCollectionIds
     * @param int $storeId
     * @return array
     */
    public function getReviewProduct($getAllCollectionIds, $storeId)
    {
        $connection = $this->resourceConnection;
        $mainTable = $connection->getTableName('catalog_product_entity');
        $tableReview = $connection->getTableName('review');
        $tableReviewDetail = $connection->getTableName('review_detail');
        $select = sprintf("SELECT e.entity_id, review.review_id, review.status_id, review.created_at,
            review_detail.detail, review_detail.nickname FROM %s AS e LEFT JOIN
            %s AS review ON e.entity_id=review.entity_pk_value LEFT JOIN %s
             AS review_detail ON review.review_id=review_detail.review_id WHERE (e.entity_id IN(
            %s )) AND (review_detail.store_id='%d')",
            $mainTable,
            $tableReview,
            $tableReviewDetail,
            $getAllCollectionIds,
            $storeId
        );
        return $connection->getConnection()->fetchAll($select);
    }

    /**
     * Get attribute config Brand and Gtin
     *
     * @param AbstractCollection $collection
     * @param int $storeId
     * @param array $attributes
     * @return array
     */
    public function getAttribute($collection, $storeId, $attributes)
    {
        $dataAttribute = [];
        $getAllCollectionIds = implode(',', $collection->getAllIds());
        $productCollection = $this->collectionFactory->create();
        $products = $productCollection->addAttributeToSelect($attributes)
            ->addFieldToFilter('entity_id', ['in' => $getAllCollectionIds])->addStoreFilter($storeId);
        foreach ($products as $product) {
            if (isset($attributes[0])) {
                $dataAttribute[$product->getId()][$attributes[0]] = $product->getData($attributes[0]);
            }
            if (isset($attributes[1])) {
                $dataAttribute[$product->getId()][$attributes[1]] = $product->getData($attributes[1]);
            }
        }
        return $dataAttribute;
    }
}
