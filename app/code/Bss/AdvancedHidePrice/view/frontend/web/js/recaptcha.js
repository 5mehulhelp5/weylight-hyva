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
define([
    'jquery',
    ], function ($) {
        return function (config) {
            var onloadCallback = function () {
                var antispam_recaptcha;
                var expCallbackReview = function () {
                    grecaptcha.reset(antispam_recaptcha);
                };
                antispam_recaptcha = grecaptcha.render('tab-antispam-recaptcha-call-for-price', {
                    'sitekey': config.siteKey,
                    'expired-callback': expCallbackReview,
                    'callback': function (response) {
                        if (response.length > 0) {
                            $('#antispam-recaptcha').attr('value', 'success');
                        }
                    }
                });
            };
            window.onloadCallback = onloadCallback;
            $.getScript(config.src);
        }
    }
);
