<?php
declare(strict_types=1);
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
 * @package    Hyva_BssConvertImageWebp
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Hyva\BssConvertImageWebp\Plugin;

use Magento\Framework\View\Element\Template;

class ChangeImageTemplate
{
    /**
     * Replace image template file
     *
     * @param Template $subject
     * @param string $template
     * @return string
     */
    public function beforeSetTemplate(Template $subject, $template)
    {
        if ($template == "Magento_Catalog::product/list/image.phtml" || $template == "Magento_Catalog::product/image.phtml") {
            $template = "Hyva_BssConvertImageWebp::product/list/image.phtml";
        }
        return $template;
    }
}
