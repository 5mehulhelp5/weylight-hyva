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
                /**
                 * Get product final price.
                 *
                 * @param {Object} row
                 * @return {HTMLElement} final price html
                 */
                getPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product regular price.
                 *
                 * @param {Object} row
                 * @return {HTMLElement} regular price html
                 */
                getRegularPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Check if product has minimal price.
                 *
                 * @param {Object} row
                 * @return {HTMLElement} minimal price html
                 */
                isMinimalPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product minimal price.
                 *
                 * @param {Object} row
                 * @return {HTMLElement} minimal price html
                 */
                getMinimalPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Check if product is salable.
                 *
                 * @param {Object} row
                 * @return {Boolean}
                 */
                isSalable: function (row) {
                    if (row['price_info']['callhide_price']) {
                        window.checkCallHideprice = true;
                        this.label = '';
                    }
                    if (!row['price_info']['final_price']) {
                        this.label = '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product maximum price.
                 *
                 * @param {Object} row
                 * @return {HTMLElement} maximum price html
                 */
                getMaxPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product maximum regular price in case of price range and special price.
                 *
                 * @param {Object} row
                 * @return {HTMLElement} maximum regular price html
                 */
                getMaxRegularPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product minimal regular price in case of price range and special price.
                 *
                 * @param {Object} row
                 * @return {HTMLElement} minimal regular price html
                 */
                getMinRegularPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product minimal price as number.
                 *
                 * @param {Object} row
                 * @return {Number} minimal price amount
                 */
                getMinimalPriceAmount: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product minimal regular price as number in case of special price.
                 *
                 * @param {Object} row
                 * @return {Number} minimal regular price amount
                 */
                getMinimalRegularPriceAmount: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product maximum price as number.
                 *
                 * @param {Object} row
                 * @return {Number} maximum price amount
                 */
                getMaximumPriceAmount: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Get product maximum regular price as number in case of special price.
                 *
                 * @param {Object} row
                 * @return {Number} maximum regular price amount
                 */
                getMaximumRegularPriceAmount: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return '';
                    }
                    if (!row['price_info']['final_price']) {
                        return '';
                    }
                    return this._super(row);
                },

                /**
                 * Check if minimal regular price exist for product.
                 *
                 * @param {Object} row
                 * @return {Boolean}
                 */
                showMinRegularPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return false;
                    }
                    if (!row['price_info']['final_price']) {
                        return false;
                    }
                    return this._super(row);
                },

                /**
                 * Check if maximum regular price exist for product.
                 *
                 * @param {Object} row
                 * @return {Boolean}
                 */
                showMaxRegularPrice: function (row) {
                    if (row['price_info']['callhide_price']) {
                        return false;
                    }
                    if (!row['price_info']['final_price']) {
                        return false;
                    }
                    return this._super(row);
                }
            });
        }
    }
);
