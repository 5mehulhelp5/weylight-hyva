<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_InventoryReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConvertImageWebp\Model\Plugin\Config;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager;

class CatalogMediaConfig
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManagerFactory;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var null
     */
    protected $catalogMediaConfig;

    /**
     * @param ObjectManagerInterface $objectManagerFactory
     * @param Manager $moduleManager
     * @param ProductMetadataInterface $productMetadata
     * @param null $catalogMediaConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManagerFactory,
        Manager $moduleManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        $catalogMediaConfig = null
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->moduleManager = $moduleManager;
        $this->catalogMediaConfig = $catalogMediaConfig;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param object $objectName
     * @param array $data
     * @return mixed|null
     */
    public function getObjectInstance($objectName, $data = [])
    {
        if ($this->isMoreThanM242()) {
            return $this->objectManagerFactory->create(
                $objectName,
                $data
            );
        }
        return null;
    }

    /**
     * Check version magento
     *
     * @return bool
     */
    public function isMoreThanM242()
    {
        if (version_compare($this->productMetadata->getVersion(), '2.4.2', '>=')) {
            return true;
        }
        return false;
    }

    /**
     * @param array $data
     * @return mixed|null
     */
    public function getCatalogMediaConfig($data = [])
    {
        return $this->getObjectInstance(
            $this->catalogMediaConfig,
            $data
        );
    }
}
