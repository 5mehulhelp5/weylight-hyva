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

namespace Bss\ProductLabel\Model\Indexer;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;

class LabelIndexer implements MviewActionInterface, IndexerActionInterface
{
    /**
     * @var ProductIndexer
     */
    private $productIndexer;

    /**
     * @var \Bss\ProductLabel\Model\Label
     */
    private $labelModel;

    /**
     * @var \Bss\ProductLabel\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * Product Label indexer id
     */
    const INDEXER_ID = 'bss_product_label';

    /**
     * LabelIndexer constructor.
     * @param \Bss\ProductLabel\Model\Label $labelModel
     * @param \Bss\ProductLabel\Helper\Data $helper
     * @param ProductIndexer $productIndexer
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        \Bss\ProductLabel\Model\Label $labelModel,
        \Bss\ProductLabel\Helper\Data $helper,
        ProductIndexer $productIndexer,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->productIndexer = $productIndexer;
        $this->labelModel = $labelModel;
        $this->helper = $helper;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Execute full indexation
     * @throws \Zend_Db_Statement_Exception
     */
    public function executeFull()
    {
        if ($this->helper->isEnable()) {
            $this->labelModel->doExecuteFull();
        }
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $productIds
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function executeList(array $productIds)
    {
        $this->doReindexProducts($productIds);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $productId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function executeRow($productId)
    {
        $this->doReindexProducts([$productId]);
    }

    /**
     * Execute materialization on ids entities, when Cron run reindex Mview
     * @param int[] $labelIds
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute($labelIds)
    {
        if ($this->helper->isEnable()) {
            $this->labelModel->doExecuteFull($labelIds);
        }
    }

    /**
     * @param [] $productIds
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function doReindexProducts($productIds)
    {
        if ($this->helper->isEnable()) {
            foreach ($productIds as $productId) {
                $this->productIndexer->execute($productId);
            }
            $this->invalidateCache();
        }
    }

    /**
     * Perform invalidate cache & indexer
     */
    protected function invalidateCache()
    {
        // mark FPC as invalidate
        $this->cacheTypeList->invalidate(
            \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
        );
    }
}
