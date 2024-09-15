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
 * @package    Bss_AdvancedHidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

var config = {
    config: {
        mixins: {
            "Magento_Catalog/js/product/list/columns/final-price" : {
                "Bss_AdvancedHidePrice/js/product/list/columns/final-price": true
            },
            "Magento_Catalog/js/product/list/columns/pricetype-box" : {
                "Bss_AdvancedHidePrice/js/product/list/columns/pricetype-box": true
            },
            "Magento_ConfigurableProduct/js/configurable" : {
                "Bss_AdvancedHidePrice/js/configurable": true
            },
            "Magento_Swatches/js/swatch-renderer" : {
                "Bss_AdvancedHidePrice/js/swatch-renderer": true
            },
        }
    },
    "map": {
        "*": {
            "Magento_Bundle/template/product/final_price.html":
                "Bss_AdvancedHidePrice/template/product/final_price.html"
        }
    }
};
