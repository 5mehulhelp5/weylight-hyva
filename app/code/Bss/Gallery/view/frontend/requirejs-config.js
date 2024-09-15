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

var config = {
    paths: {
        'bss_fancybox': 'Bss_Gallery/js/fancybox/source/jquery.fancybox',
        'bss_owlslider': 'Bss_Gallery/js/owl.carousel.2.3.4/owl.carousel.min',
    },
    shim: {
        'bss_fancybox': {
            deps: ['jquery']
        },
        'bss_owlslider': {
            deps: ['jquery']
        }
    }
};
require.config(config);