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
define(
    [
    'jquery',
    "domReady!"
],
    function ($) {
    'use strict';

    var mixin = {
        initialize: function (config, element) {
            if ($.type(canDisplayWebp) !== "undefined" && canDisplayWebp) {
                this._replaceImageDataWithWebp(config.data);
            }
            this._super(config, element);
        },

        _replaceImageDataWithWebp: function (imagesData) {
            if (_.isEmpty(imagesData)) {
                return;
            }

            $.each(imagesData, function (key, imageData) {
                if (imageData['full_webp'] && imageData['img_webp'] && imageData['thumb_webp']) {
                    imageData['full'] = imageData['full_webp'];
                    imageData['img'] = imageData['img_webp'];
                    imageData['thumb'] = imageData['thumb_webp'];
                }
            });
        },
    };

    return function (target) {
        return target.extend(mixin);
    }
});
