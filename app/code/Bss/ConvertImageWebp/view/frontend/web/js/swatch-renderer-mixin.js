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
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            /**
             * Set img default use tag <source> in <picture>.
             *
             * @private
             */
            _create: function () {
                if ($.type(canDisplayWebp) !== "undefined" && canDisplayWebp) {
                    var dataImages = this.options.jsonConfig.images;

                    $.each(dataImages, function (productId, dataImg) {
                        $.each(dataImg, function (key, value) {
                            if (value['full_webp_child'] && value['img_webp_child'] && value['thumb_webp_child']) {
                                dataImages[productId][key]['full'] = value['full_webp_child'];
                                dataImages[productId][key]['img'] = value['img_webp_child'];
                                dataImages[productId][key]['thumb'] = value['thumb_webp_child'];
                            }
                        });
                    });
                }

                this._super();

                //Set img default use tag <source> in <picture>.
                var productData = this._determineProductData();
                if (!productData.isInProductView) {
                    var $main = productData.isInProductView ?
                        this.element.parents('.column.main') :
                        this.element.parents('.product-item-info');

                    if (!$main.find('.product-image-photo').attr('src')
                        && $main.find('.product-image-photo').attr('srcset')
                    ) {
                        this.options.mediaGalleryInitial = [{
                            'img': $main.find('source.product-image-photo').attr('srcset'),
                            'imgOld': $main.find('img.product-image-photo').attr('src'),
                        }];
                    }
                }
            },
            /**
             * Update srcset in picture tag html.
             *
             * @param {Array} images
             * @param {jQuery} context
             * @param {Boolean} isInProductView
             */
            updateBaseImage: function (images, context, isInProductView) {
                this._super(images, context, isInProductView);

                if(!isInProductView) { // No processed in product page.
                    // Change image in tag <source> to webp img
                    var sourceSelector = context.find('source');
                    if (sourceSelector.length > 0 && images[0]) {
                        if (images[0].img) {
                            sourceSelector.removeAttr('src');
                            sourceSelector.attr('srcset', images[0].img);

                            var type = images[0].img.split('.').pop();
                            sourceSelector.attr('type', 'image/' + type);
                        }
                    }

                    // Change image in tag <img> to default img
                    var imgSelector = context.find('img');
                    if (imgSelector.length > 0 && images[0]) {
                        if (images[0].imgOld) { // link img when not selected option
                            imgSelector.attr('src', images[0].imgOld);
                        } else if (!images[0].img.includes(".webp")) { // link img default
                            imgSelector.attr('src', images[0].img);
                        } else if (images[1] && images[1].img && !images[1].img.includes(".webp")) { // link img default
                            imgSelector.attr('src', images[1].img);
                        }
                    }
                }
            }
        });

        return $.mage.SwatchRenderer;
    }
});
