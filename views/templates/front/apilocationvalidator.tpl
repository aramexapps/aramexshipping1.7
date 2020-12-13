{*
* 2007-2019 PrestaShop
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

{if $allowed eq '1'}
    <script type="text/javascript">

        $(document).ready(function () {
            var type = 'delivery-address';
            Go(type);
            var type = 'invoice-address';
            Go(type);

            function Go(type) {
                var button = '#' + type + ' .continue';
                var shippingAramexCitiesObj;

                $("#" + type).find('input[name^= "city"]').after('<div id="aramex_loader" style="height:31px; width:31px; display:none;"></div>');
                /* get Aramex sities */
                shippingAramexCitiesObj = AutoSearchControls(type, "");
                $("#" + type).find('select[name^= "id_country"]').change(function () {
                    $("#" + type).find('input[name^= "city"]').val("");
                    getAllCitiesJson(type, shippingAramexCitiesObj);
                });
                getAllCitiesJson(type, shippingAramexCitiesObj);

                function AutoSearchControls(type, search_city) {
                    return $("#" + type).find('input[name^= "city"]')
                        .autocomplete({
                            /*source: search_city,*/
                            minLength: 3,
                            scroll: true,
                            source: function (req, responseFn) {
                                var re = $.ui.autocomplete.escapeRegex(req.term);
                                var matcher = new RegExp("^" + re, "i");
                                var a = $.grep(search_city, function (item, index) {
                                    return matcher.test(item);
                                });
                                responseFn(a);
                            },
                            search: function (event, ui) {
                                /* open initializer */
                                $('#' + type + ' .ui-autocomplete').css('display', 'none');
                                $('#' + type + ' #aramex_loader').css('display', 'block');
                            },
                            response: function (event, ui) {
                                var temp_arr = [];
                                $(ui.content).each(function (i, v) {
                                    temp_arr.push(v.value);
                                });
                                $('#' + type + ' #aramex_loader').css('display', 'none');
                                return temp_arr;
                            }
                        });
                }

                function getAllCitiesJson(type, aramexCitiesObj) {
                    var country_code = $('#' + type).find('select[name^= "id_country"]').val();
                    var url_check = "{url entity='module' name='aramexshipping' controller='serchautocities'}?country_code=" + country_code;
					aramexCitiesObj.autocomplete("option", "source", url_check);
                }

                /* make validation */
                bindIvents(type, button);

                function bindIvents(type, button) {
                    $('#' + type).find('input[name^= "city"]').blur(function () {
                        addressApiValidation(type, button);
                    });
                    $('#' + type).find('input[name^= "address1"]').blur(function () {
                        addressApiValidation(type, button);
                    });
                    $('#' + type).find('input[name^= "postcode"]').blur(function () {
                        addressApiValidation(type, button);
                    });

                }

                function addressApiValidation(type, button) {
                    var chk_city = $('#' + type).find('input[name^= "city"]').val();
                    var address1 = $('#' + type).find('input[name^= "address1"]').val();
                    var chk_postcode = $('#' + type).find('input[name^= "postcode"]').val();
                    var country_code = $('#' + type).find('select[name^= "id_country"]').val();
                    if (address1 == '' || chk_city == '' || chk_postcode == '') {
                        return false;
                    } else {
                        $(button).prop("disabled", true);
                        $(button).css('background-color', '#2fb000');
                        $(button).html('Loading ...');

                        $.ajax({
                            url: "{url entity='module' name='aramexshipping' controller='applyvalidation'}",
                            data: { city: chk_city, post_code: chk_postcode, country_code: country_code },
                            type: 'Post',
                            success: function (result) {
                                var response = JSON.parse(result);
                                if (!(response.suggestedAddresses) && response.message != '' && response.message !== undefined) {
                                    if (response.message.indexOf("City") != -1) {
                                        if ($('#' + type).find('input[name^= "city"]').val() != "") {
                                            if (response.message !== undefined) {
                                                alert(response.message);
                                            }
                                        }
                                        $('#' + type).find('input[name^= "city"]').val("");
                                    }
                                    if (response.message.indexOf("zip") != -1) {
                                        if ($('#' + type).find('input[name^= "postcode"]').val() != "") {
                                            if (response.message !== undefined) {
                                                alert(response.message);
                                            }
                                        }
                                        $('#' + type).find('input[name^= "postcode"]').val("");
                                    }
                                } else if (response.suggestedAddresses) {
                                    //response.suggestedAddresses.City
                                    $('#' + type).find('input[name^= "city"]').val("");

                                }
                                $(button).prop("disabled", false);
                                $(button).css('background-color', '#2fb5d2');
                                $(button).html('Continue');
                            }
                        })
                    }
                }
            }
        });

    </script>
    <style>
        #aramex_loader {
            background-image: url('{$dir_url|escape:'html':'UTF-8'}/views/img/aramex_loader.gif');
        }

        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            /* prevent horizontal scrollbar */
            overflow-x: hidden;
            /* add padding to account for vertical scrollbar */
        }

        .required-aramex:before {
            content: '* ' !important;
            color: #F00 !important;
            font-weight: bold !important;
        }
    </style>
{/if}