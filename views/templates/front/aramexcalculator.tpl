{*
* 2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $aramexcalculator eq '1'}
    <div class="aramex_popup" style="display:none;" data-popup="popup-1">
        <div class="aramex_popup-inner">
            <form method="post" class="form-horizontal" action="">
                <h2 style="color: #EA7601;">Check Aramex Shipping Rate</h2>
                <h3>Shipment Destination</h3>
                <div class="form-group">
                    <label for="destination_country" class=" col-sm-3 control-label">Country</label>
                    <div class="col-sm-9">
                        <select name="destination_country" class="form-control" id="destination_country">
                            {if  count($countries) > 0}
                                {foreach from=$countries key=key item=_country}
                                    {if isset($customer_country)}
                                        {if $customer_country eq $_country}
                                            {assign var = 'selected_str' value = 'selected="selected"'}
                                        {else}
                                            {assign var = 'selected_str' value = ""}
                                        {/if}
                                    {else}
                                        {assign var = 'selected_str' value = ""}
                                    {/if}
                                        <option
                                                {$selected_str|escape:'html':'UTF-8'}
                                                value="{$_country|escape:'html':'UTF-8'}"
                                                id="{$key|escape:'html':'UTF-8'}"
                                        >{$key|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">City</label>
                    <div class="col-sm-9">
                        {if isset($customer_city)}
                            {assign var = 'customer_city'  value = $customer_city }
                        {else}
                            {assign var = 'customer_city' value = ""}
                        {/if}
                        <input name="destination_city" class="form-control" autocomplete="off" id="destination_city"
                               value="{$customer_city|escape:'html':'UTF-8'}"/>
                        <div id="destination_city_loading_autocomplete" class="loading_autocomplete"
                             style="display:none;">
                            {assign var = 'loading_cities' value = "Loading cities..."}
                            <img style="height:30px;"
                                 src="{$aramex_loader|escape:'html':'UTF-8'}"
                                 alt="{$loading_cities|escape:'html':'UTF-8'}"
                                 title="{$loading_cities|escape:'html':'UTF-8'}"
                                 class="v-middle"/>
                        </div>
                    </div>

                </div>
                <div class="form-group">
                    <div class="field fl width-270">
                        <label class="col-sm-3 control-label">Zip code</label>
                        <div class="col-sm-9">
                            {if isset($customer_postcode)}
                                {assign var = 'customer_postcode'  value = $customer_postcode }
                            {else}
                                {assign var = 'customer_postcode' value = ""}
                            {/if}
                            <input name="destination_zipcode" class="form-control" id="destination_zipcode"
                                   value="{$customer_postcode|escape:'html':'UTF-8'}"/>
                        </div>
                    </div>
                </div>
                <div class="aramex_field aramex_result_block">
                    <h3 style="display:none; color: #EA7601;">Result</h3>
                    <div class="aramex_result mar-10">
                    </div>
                    {assign var = 'wait' value = "Please wait..."}
                    <span class="aramex-please-wait" id="payment-please-wait" style="display:none;">
                        <img src="{$preloader|escape:'html':'UTF-8'}"
                             alt="{$wait|escape:'html':'UTF-8'}"
                             title="{$wait|escape:'html':'UTF-8'}"
                             class="v-middle"/> {$wait|escape:'html':'UTF-8'}
                    </span>
                </div>
                <div class="form-group">
                    <button name="aramex_calc_rate_submit" class="btn btn-primary btn-lg btn-block" type="button"
                            id="aramex_calc_rate_submit"
                            onclick="sendAramexRequest({$product_id|escape:'htmlall':'UTF-8'})">Calculate
                    </button>
                </div>
            </form>
            <a class="aramex_popup-close" data-popup-close="popup-1" href="#">x</a>
        </div>
    </div>
    <script>

        function sendAramexRequest() {
            var chk_city = jQuery('#destination_city').val();
            var chk_postcode = jQuery('#destination_zipcode').val();
            var country_code = jQuery("#destination_country").val();
            var currency = "{$currency1|escape:'html':'UTF-8'}";
            var product_id = "{$product_id|escape:'html':'UTF-8'}";
			var url = "{$link->getModuleLink('aramexshipping','aramexcalculator')|escape:'quotes':'UTF-8'}";
            jQuery('.aramex_result_block h3').css("display", "none");
            jQuery('.aramex-please-wait').css("display", "block");
            jQuery('.aramex_result').css("display", "none");
            jQuery.ajax({
                url: url,
                data: {
                    city: chk_city,
                    post_code: chk_postcode,
                    country_code: country_code,
                    product_id: product_id,
                    currency: currency
                },
                type: 'Post',
                success: function (result) {
                    var message = "";
                    var response = jQuery.parseJSON(result);
                    console.log(response.type);
                    if (response.type == 'error') {
                        jQuery.each(response, function (index, value) {
                            if (typeof value != 'undefined' && value != 'error') {
                                message = message + "<p style='color: rgb(255,0,0);'>" + value  + "</p>";
                                return false;
                            }
                        });
                    } else {
                        jQuery.each(response, function (index, value) {
                            if (typeof value.label != 'undefined') {
                                message = message + "<p style='color: rgb(234, 118, 1);'>" + value.label + ": " + value.amount + " " + value.currency + "</p>";
                            }
                        });
                    }
                    jQuery('.aramex_result_block h3').css("display", "block");
                    jQuery('.aramex_result').css("display", "block").html(message);
                    jQuery('.aramex-please-wait').css("display", "none");

                }
            });
        }

        (function ($) {
            $(document).ready(function () {
                $(function () {
                    //----- OPEN
                    $('[data-popup-open]').on('click', function (e) {
                        var targeted_popup_class = $(this).attr('data-popup-open');
                        $('[data-popup="' + targeted_popup_class + '"]').fadeIn(350);
                        e.preventDefault();
                    });

                    //----- CLOSE
                    $('[data-popup-close]').on('click', function (e) {
                        var targeted_popup_class = $(this).attr('data-popup-close');
                        $('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);
                        e.preventDefault();
                    });
                });

                var type = '.aramex_popup-inner';
                var shippingAramexCitiesObj;
                var shipping_aramex_cities = "";

                /* get Aramex sities */
                shippingAramexCitiesObj = AutoSearchControls(type, shipping_aramex_cities);
                jQuery(type).find("select[name^='destination_country']").change(function () {
                    getAllCitiesJson(type, shippingAramexCitiesObj);
                });
                getAllCitiesJson(type, shippingAramexCitiesObj);

                function AutoSearchControls(type, search_city) {
                    return jQuery(type).find("input[name^='destination_city']")
                        .autocomplete({
                            /*source: search_city,*/
                            minLength: 3,
                            scroll: true,
                            source: function (req, responseFn) {
                                var re = $.ui.autocomplete.escapeRegex(req.term);
                                var matcher = new RegExp("^" + re, "i");
                                var a = jQuery.grep(search_city, function (item, index) {
                                    return matcher.test(item);
                                });
                                responseFn(a);
                            },
                            search: function (event, ui) {
                                /* open initializer */
                                jQuery('.checkout-index-index .ui-autocomplete').css('display', 'none');
                                jQuery(type + ' .loading_autocomplete').css('display', 'block');
                            },
                            response: function (event, ui) {
                                var temp_arr = [];
                                jQuery(ui.content).each(function (i, v) {
                                    temp_arr.push(v.value);
                                });

                                jQuery(type + ' .loading_autocomplete').css('display', 'none');
                                return temp_arr;
                            }
                        });
                }

                function getAllCitiesJson(type, aramexCitiesObj) {
                    var country_code = jQuery(type).find("select[name^='destination_country']").val();
                    var url_check = "{$link->getModuleLink('aramexshipping', 'serchautocities')|escape:'quotes':'UTF-8'}?country_code=" + country_code;
                    shipping_aramex_cities_temp = '';
                    aramexCitiesObj.autocomplete("option", "source", url_check);
                }

            });
        })(jQuery);
    </script>
    <style>
        .ui-autocomplete {
            z-index: 99999999;
        }

        .content {
            max-width: 800px;
            width: 100%;
            margin: 0px auto;
            margin-bottom: 60px;
        }

        /* Outer */
        .aramex_popup {
            width: 100%;
            height: 100%;
            display: none;
            position: fixed;
            top: 0px;
            left: 0px;
            background: rgba(0, 0, 0, 0.75);
            z-index: 9999;
        }

        /* Inner */
        .aramex_popup-inner {
            padding: 40px;
            position: absolute;
            top: 50%;
            left: 50%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            box-shadow: 0px 2px 6px rgba(0, 0, 0, 1);
            border-radius: 3px;
            background: #fff;
        }

        /* Close Button */
        .aramex_popup-close {
            width: 30px;
            height: 30px;
            padding-top: 4px;
            display: inline-block;
            position: absolute;
            top: 0px;
            right: 0px;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
            border-radius: 1000px;
            background: rgba(0, 0, 0, 0.8);
            font-family: Arial, Sans-Serif;
            font-size: 20px;
            text-align: center;
            line-height: 100%;
            color: #fff;
        }

        .aramex_popup-close:hover {
            -webkit-transform: translate(50%, -50%) rotate(180deg);
            transform: translate(50%, -50%) rotate(180deg);
            background: rgba(0, 0, 0, 1);
            text-decoration: none;
        }

        .aramex_popup .aramex_field {
            padding: 10px;
        }

        .aramex_popup select {
            padding: 5px;
        }
        .aramex_popup label {
           text-align: left;
        }
        .aramex_popup  .form-group{
            overflow: hidden;
        }

        .aramex_popup-inner button, .aramex_popup-inner input, .aramex_popup-inner select, .aramex_popup-inner table, .aramex_popup-inner textarea {
            font-family: Arial !important;
        }

    </style>
{/if}