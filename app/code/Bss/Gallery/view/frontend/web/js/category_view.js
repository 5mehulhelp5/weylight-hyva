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
    'bss_fancybox',
], function ($) {
    "use strict";
    return function (config) {
        $('#gallery-wrapper').find('.product-image-photo').removeClass('product-image-photo');
        $('.fancybox').attr('data-index', 'images').fancybox({
            slideShow: {
                autoStart: config.isAutoLoad,
                speed: config.pageSpeed
            },
            baseClass: 'bss_' + config.titlePosition,
            transitionEffect :config.nextEffect,
            preventCaptionOverlap: true,
            selector : '.owl-item:not(.cloned) a',
            backFocus : false,
            hash   : false,
            thumbs : {
                showOnStart : true
            },
            buttons : [
                'zoom',
                'download',
                'close'
            ],

            afterShow: function(instance, slide) {
                if (config.titlePosition === 'inside') {
                    $('.fancybox-slide .fancybox-content .fancybox-caption.fancybox-caption--separate').remove();
                    $('.fancybox-caption.fancybox-caption--separate').clone().appendTo('.fancybox-slide--current .fancybox-content');
                    $('.fancybox-inner > .fancybox-caption.fancybox-caption--separate').css('display', 'none');
                }
                if (config.titlePosition === 'over') {
                    $('.fancybox-slide .fancybox-content .fancybox-caption.fancybox-caption--separate').remove();
                    $('.fancybox-caption.fancybox-caption--separate').clone().appendTo('.fancybox-slide--current .fancybox-content');
                    $('.fancybox-inner > .fancybox-caption.fancybox-caption--separate').css('display', 'none');
                }
                $('.fancybox-caption').css('font-weight','bold');

            }
        });
        $(document).on('click', '.owl-item.cloned a', function(e) {
            var $slides = $(this)
                .parent()
                .siblings('.owl-item .active');
            $slides
                .eq( ( $(this).attr("data-index") || 0) % $slides.length )
                .find('a')
                .trigger("click.fb-start", { $trigger: $(this) });

            return false;
        });
    };
});
