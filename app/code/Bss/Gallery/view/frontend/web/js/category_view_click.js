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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'bss_owlslider'
], function ($) {
    "use strict";
    return function (config) {
        $('#gallery-wrapper').find('.product-image-photo').removeClass('product-image-photo');
        var owl = $('ul.gallery_slider_category_view');
        owl.owlCarousel({
            'autoplayHoverPause': true,
            'loop': true,
            'margin': 20,
            'autoHeight': true,
            navText: ["<span aria-label='Previous'>Prev</span>", "<span aria-label='Next'>Next</span>"],
            'nav': true,
            'dot': true,
            'responsiveClass': true,
            'responsive': {
                '0': {
                    items: 1,
                },
                '600': {
                    items: 3,
                },
                '960': {
                    items: 4,
                },
                '1200': {
                    items: 5,
                }
            },
            'autoplay': config.Category,
            'autoplayTimeout': config.pageSpeed
        });
        owl.on('translated.owl.carousel', function(event) {
            var $owlItems = $(this).find('.owl-item.cloned.active');
            if($owlItems.length){
                $owlItems.each(function (index,value) {
                    value.classList.remove('cloned');
                })
            }
        });
    };
});
