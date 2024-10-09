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
 * @package    Hyva_BssHtmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-present BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
 
declare(strict_types=1);

namespace Hyva\BssHtmlSiteMap\ViewModel;

use Bss\HtmlSiteMap\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Helper implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Get  Html SiteMap Helper
     *
     * @return Data
     */
    public function getHtmlSiteMapHelper()
    {
        return $this->helperData;
    }
}
