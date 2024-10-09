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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'bss_fancybox',
], function ($) {
    'use strict';

    $.widget('bss.bss_gallery_fancy', {
        options: {
            isAutoLoad:'',
            pageSpeed:'',
            nextEffect:'',
            titlePosition:'',
            getBaseUrl:'',
            getLimit:'',
        },
        _create: function () {
            var $this = this;
            $('#gallery-wrapper').find('.product-image-photo').removeClass('product-image-photo');
            this.loadFancybox();
            $('.gallery-category-list-item-ajax').click(function () {
                $('body').trigger('processStart');
                $('#load-more').hide();
                $('.gallery-category-list-item-ajax.active').removeClass('active');
                $(this).addClass('active');
                var cateItemsIds = $(this).attr('item-ids').split(',');
                var cateId = $(this).attr('cate-id');
                $.ajax({
                    url: $this.options.getBaseUrl + 'gallery/index/ajax',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'cateId': cateId
                    }
                }).done(function (data) {
                    var count = 0;
                    var limit = parseInt($this.options.getLimit);
                    $('#gallery-loading').hide();
                    if (data != null) {
                        $('.gallery-category-list-content').html(data);
                        $this.loadFancybox();
                    }
                    if (cateItemsIds.length <= (count + limit) || cateItemsIds.length == (count + limit)) {
                        $('#load-more').hide();
                    } else {
                        $('#load-more').show();
                    }
                    $('body').trigger('processStop');
                    $('.gallery-category-item-description').each(function () {
                        if ($(this).attr('cate-id') == cateId) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }).fail(function () {
                    $('body').trigger('processStop');
                    $('.gallery-category-list .gallery-category-list-item').each(function () {
                        if (cateItemsIds.indexOf($(this).attr('item-id')) != -1) {
                            $(this).show();
                            $(this).find('a.fancybox').attr('rel', 'gallery-' + cateId);
                        } else {
                            $(this).hide();
                        }
                    });
                    $('#load-more').hide();
                })
            });

            $('#load-more').click(function () {
                $('body').trigger('processStart');
                $('#load-more').hide();
                var cateItemsIds = $('.gallery-category-list-item-ajax.active').attr('item-ids').split(',');
                var cateId = $('.gallery-category-list-item-ajax.active').attr('cate-id');
                var itemIds = [];
                $('.gallery-category-list .gallery-category-list-item').each(function () {
                    var id = $(this).attr('item-id');
                    itemIds.push(id);
                });
                $.ajax({
                    url: $this.options.getBaseUrl + 'gallery/index/ajax',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'itemIds': itemIds,
                        'cateId': cateId
                    }
                }).done(function (data) {
                    $('body').trigger('processStop');
                    var count = 0;
                    var limit = parseInt($this.options.getLimit);
                    $('.gallery-category-list .gallery-category-list-item').each(function () {
                        if (cateItemsIds.indexOf($(this).attr('item-id')) != -1) {
                            $(this).show();
                            $(this).find('a.fancybox').attr('rel', 'gallery-' + cateId);
                            count++;
                        } else {
                            $(this).hide();
                        }
                    });
                    if (data != null) {
                        $('.gallery-category-list-content').append(data);
                        $this.loadFancybox();
                    }
                    if (cateItemsIds.length <= (count + limit) || cateItemsIds.length == (count + limit)) {
                        $('#load-more').hide();
                    } else {
                        $('#load-more').show();
                    }
                }).fail(function () {
                    $('body').trigger('processStop');
                    $('.gallery-category-list .gallery-category-list-item').each(function () {
                        if (cateItemsIds.indexOf($(this).attr('item-id')) != -1) {
                            $(this).show();
                            $(this).find('a.fancybox').attr('rel', 'gallery-' + cateId);
                        } else {
                            $(this).hide();
                        }
                        $('#load-more').hide();
                    });
                });
            });
        },

        loadFancybox: function () {
            var $this = this;
            $('.fancybox').attr('data-fancybox', 'gallery').fancybox({
                slideShow: {
                    autoStart: $this.options.isAutoLoad,
                    speed: $this.options.pageSpeed
                },
                baseClass: "bss_" + $this.options.titlePosition,
                transitionEffect :$this.options.nextEffect,
                preventCaptionOverlap: true,

                afterShow: function(instance, slide) {
                    if ($this.options.titlePosition === 'inside') {
                        $('.fancybox-slide .fancybox-content .fancybox-caption.fancybox-caption--separate').remove();
                        $('.fancybox-caption.fancybox-caption--separate').clone().appendTo('.fancybox-slide--current .fancybox-content');
                        $('.fancybox-inner > .fancybox-caption.fancybox-caption--separate').css('display', 'none');
                    }
                    if ($this.options.titlePosition === 'over') {
                        $('.fancybox-slide .fancybox-content .fancybox-caption.fancybox-caption--separate').remove();
                        $('.fancybox-caption.fancybox-caption--separate').clone().appendTo('.fancybox-slide--current .fancybox-content');
                        $('.fancybox-inner > .fancybox-caption.fancybox-caption--separate').css('display', 'none');
                    }
                    $('.fancybox-caption').css('font-weight','bold');

                }
            });
        },


    });
    return $.bss.bss_gallery_fancy;
});
