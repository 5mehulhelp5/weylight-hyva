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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    ],
    function () {
        'use strict';
        return function (Component) {
            return Component.extend({
                hasCallHidePrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return false;
                    }
                    if (!row['price_info']['final_price']) {
                        return false;
                    }
                    return true;
                }
            });
        }
    }
);
