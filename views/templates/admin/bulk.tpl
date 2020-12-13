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

<div class="order_in_background" style="display:none;">
    <div class="aramex_bulk">
        <p><strong>Shipment Default Information </strong></p>
        <form id="massform">
            <div class="aramex_shipment_creation_part_left">
                <FIELDSET class="aramex_shipment_creation_fieldset_big">
                    <div class="text_short">
                        <label><strong>Domestic Product Group</strong></label>
                        <input class="aramex_all_options" id="aramex_shipment_info_product_group"
                               name="aramex_shipment_info_product_group_dom" type="hidden" value="DOM"/>
                    </div>
                    <div class="text_short">
                        <label>Service Type</label>
                        <select class="aramex_all_options" id="aramex_shipment_info_product_type"
                                name="aramex_shipment_info_product_type_dom">
                            {if count($allowed_domestic_methods) > 0}
                                {foreach from=$allowed_domestic_methods key=key item=value}
                                    <option
                                            value="{$key|escape:'html':'UTF-8'}"
                                            id="{$key|escape:'html':'UTF-8'}"
                                    >{$value|escape:'html':'UTF-8'}</option>
                                {/foreach}

                            {/if}

                        </select>
                    </div>
                    <div class="text_short">
                        <label>Additional Services</label>
                        <select class="aramex_all_options" id="aramex_shipment_info_service_type"
                                name="aramex_shipment_info_service_type_dom">
                            <option value=""></option>
                            {if count($allowed_domestic_additional_services) > 0}
                                {foreach from=$allowed_domestic_additional_services key=key item=value}
                                    <option
                                            value="{$key|escape:'html':'UTF-8'}"
                                            id="dom_as_{$key|escape:'html':'UTF-8'}"
                                    >{$value|escape:'html':'UTF-8'}</option>
                                {/foreach}

                            {/if}

                        </select>
                    </div>
                    <div class="text_short">
                        <label>Payment Type</label>
                        <select class="aramex_all_options" id="aramex_shipment_info_payment_type"
                                name="aramex_shipment_info_payment_type_dom">
                            <option value="P">Prepaid</option>
                            <option value="C">Collect</option>
                            <option value="3">Third Party</option>
                        </select>
                    </div>
                    <div class="text_short">
                        <label>Currency</label><br>
                        <input type="text" class="" id="aramex_shipment_currency_code"
                               name="aramex_shipment_currency_code_dom"
                               value="{$currencyCode|escape:'html':'UTF-8'}"/>
                    </div>
                </FIELDSET>
            </div>
            <div class="aramex_shipment_creation_part_right">
                <FIELDSET class="aramex_shipment_creation_fieldset_big">
                    <div class="text_short">
                        <label><strong>International Product Group</strong></label>
                        <input class="aramex_all_options" id="aramex_shipment_info_product_group"
                               name="aramex_shipment_info_product_group" type="hidden" value="EXP"/>
                    </div>
                    <div class="text_short">
                        <label>Service Type</label><br/>
                        <select class="aramex_all_options" id="aramex_shipment_info_product_type"
                                name="aramex_shipment_info_product_type">
                            {if count($allowed_international_methods) > 0}
                                {foreach from=$allowed_international_methods key=key item=value}
                                    <option
                                            value="{$key|escape:'html':'UTF-8'}"
                                            id="{$key|escape:'html':'UTF-8'}"
                                    >{$value|escape:'html':'UTF-8'}</option>
                                {/foreach}

                            {/if}
                        </select>
                    </div>
                    <div class="text_short">
                        <label>Additional Services</label><br/>
                        <select class="aramex_all_options" id="aramex_shipment_info_service_type"
                                name="aramex_shipment_info_service_type">
                            <option value=""></option>
                            {if count($allowed_international_additional_services) > 0}
                                {foreach from=$allowed_international_additional_services key=key item=value}
                                    <option
                                            value="{$key|escape:'html':'UTF-8'}" class="non-local"
                                            id="exp_as_{$key|escape:'html':'UTF-8'}"
                                    >{$value|escape:'html':'UTF-8'}</option>
                                {/foreach}

                            {/if}

                        </select>
                    </div>
                    <div class="text_short">
                        <label>Payment Type</label><br/>
                        <select class="aramex_all_options" id="aramex_shipment_info_payment_type"
                                name="aramex_shipment_info_payment_type">
                            <option value="P">Prepaid</option>
                            <option value="C">Collect</option>
                            <option value="3">Third Party</option>
                        </select>
                        <div id="aramex_shipment_info_service_type_div" style="display: none;"></div>
                    </div>
                    <div class="text_short">
                        <label>Payment Option</label><br/>
                        <select class="" id="aramex_shipment_info_payment_option"
                                name="aramex_shipment_info_payment_option">
                            <option value=""></option>
                            <option id="ASCC" value="ASCC" style="display: none;">Needs Shipper Account Number to be
                                filled
                            </option>
                            <option id="ARCC" value="ARCC" style="display: none;">Needs Consignee Account Number to
                                be
                                filled
                            </option>
                            <option id="CASH" value="CASH">Cash</option>
                            <option id="ACCT" value="ACCT">Account</option>
                            <option id="PPST" value="PPST">Prepaid Stock</option>
                            <option id="CRDT" value="CRDT">Credit</option>
                        </select>
                    </div>
                    <div class="text_short">
                        <label>Custom Amount</label><br/>
                        <input class="" type="text" id="aramex_shipment_info_custom_amount"
                               name="aramex_shipment_info_custom_amount" value=""/>
                    </div>
                    <div class="text_short">
                        <label>Currency</label><br/>
                        <input type="text" class="" id="aramex_shipment_currency_code"
                               name="aramex_shipment_currency_code"
                               value="{$currencyCode|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="aramex_clearer"></div>
                </FIELDSET>
            </div>
            <div class="aramex_clearer"></div>
            <div class="aramex_result"></div>
            <div class="aramex_clearer"></div>
            <input name="aramex_return_shipment_creation_date" type="hidden" value="create"/>
            <div class="aramex_loader"
                 style="background-image: url({$preloader|escape:'html':'UTF-8'}); height:60px; margin:10px 0; background-position-x: center; display:none; background-repeat: no-repeat; ">
            </div>
            <div style="float: right;font-size: 11px;margin-bottom: 10px;width: 184px;">
                <input style="float: left; width: auto; height:16px; display:block; margin-top:0px;" type="checkbox"
                       name="aramex_email_customer" value="yes"/>
                <span style="float: left; margin-top: -2px;">Notify customer by email</span>
            </div>
        </form>
        <button id="aramex_shipment_creation_submit_id" type="button" class="primary  button-primary "
                name="aramex_shipment_creation_submit">Create
            Bulk Shipment
        </button>
        <button class="aramexclose primary  button-primary " type="button ">Close</button>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        $("<a class=' page-title-action  btn btn-primary' style='margin-bottom:10px;' id='create_aramex_shipment'>Bulk Aramex Shipment </a>").insertAfter(".panel-heading");
    });

    $(document).ready(function () {
        $("#create_aramex_shipment").click(function () {
            $(".order_in_background").fadeIn(500);
            $(".aramex_bulk").fadeIn(500);
        });

        $("#aramex_shipment_creation_submit_id").click(function () {
            aramexsend();
        });

        $(".aramexclose").click(function () {
            aramexclose();
        });

        $('.baulk_aramex_shipment').click(function () {
            aramexmass();
        });
    });

    function aramexclose() {
        $(".order_in_background").fadeOut(500);
        $(".aramex_bulk").fadeOut(500);
    }

    function aramexredirect() {
        window.location.reload(true);
    }

    function aramexsend() {
        var selected = [];
        var str = $("#massform").serialize();
        $('.order input:checked').each(function () {
            selected.push($(this).val());
        });
        if (selected.length === 0) {
            alert("Select orders, please");
            return false;
        }
        //  aramexclose();
        $('.popup-loading').css('display', 'block');
        $('.aramex_loader').css('display', 'block');

        var link_to_calc = "{$link->getAdminLink('AdminBulk',true)|escape:'html':'UTF-8'}";
        var parts = link_to_calc.split("=").pop();
        var link = "{$link->getAdminLink('AdminBulk',false)|escape:'html':'UTF-8'}";
        $.ajax({
            url: link+"&token="+parts,
            type: "POST",
            data: { selectedOrders: selected, str: str, bulk: "bulk" },
            success: function ajaxViewsSection(data) {
                var rr = JSON.parse(data);
                $('.popup-loading').css('display', 'none');
                $('.aramex_loader').css('display', 'none');
                $(".aramex_result").empty().css('display', 'none');
                $(".order_in_background").fadeIn(500);
                $(".aramex_bulk").fadeIn(500);
                $(".aramex_result").css("display", "block");
                $(".aramex_result").append(rr.message);
                $(".aramexclose").click(function () {
                    aramexredirect();
                });
            }
        });
    }

</script>
