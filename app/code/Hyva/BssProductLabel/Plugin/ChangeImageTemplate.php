<?php
declare(strict_types=1);
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
 * @package   Hyva_BssProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Hyva\BssProductLabel\Plugin;

class ChangeImageTemplate
{
    /**
     * Replace image template file
     *
     * @param \Magento\Framework\View\Element\Template $subject
     * @param string $template
     * @return string
     */
    public function beforeSetTemplate(\Magento\Framework\View\Element\Template $subject, $template)
    {
        if ($template == "Magento_Catalog::product/image.phtml") {
            $template = "Hyva_BssProductLabel::product/image.phtml";
        }
        return $template;
    }
}
