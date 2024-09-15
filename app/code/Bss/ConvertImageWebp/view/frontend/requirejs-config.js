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
 * @package    Bss_ConvertImageWebp
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
var config = {
    config: {
        mixins: {
            'mage/gallery/gallery': {
                'Bss_ConvertImageWebp/js/gallery-mixin': true
            },
            "Magento_Swatches/js/swatch-renderer" : {
                "Bss_ConvertImageWebp/js/swatch-renderer-mixin": true
            },
        }
    }
};
