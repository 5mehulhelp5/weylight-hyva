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
 * @category  BSS
 * @package   Hyva_BssAdvancedHidePrice
 * @author    Extension Team
 * @copyright Copyright (c) 2023-present BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Hyva\BssAdvancedHidePrice\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Bss\AdvancedHidePrice\Helper\Data;
use Magento\Catalog\Helper\Output;

class Helper implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * Construct
     *
     * @param Data $data
     * @param Output $catalogHelper
     */
    public function __construct(
        Data $data,
        Output $catalogHelper
    ) {
        $this->data = $data;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Get original module helper
     *
     * @return Data
     */
    public function getAdvancedHidePriceHelper()
    {
        return $this->data;
    }

    /**
     * Get catalog helper output
     *
     * @return Output
     */
    public function getCatalogHelper()
    {
        return $this->catalogHelper;
    }
}
