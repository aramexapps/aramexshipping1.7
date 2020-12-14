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

<div style="clear:both; padding-top:10px;">
    <div class="row">

        <a class=' btn btn-primary' style="margin-top:15px; "
           id="create_aramex_shipment">{l s='Prepare Aramex Shipment' mod='aramexshipping'}</a>
        {if  $aramex_return_button  eq true}
            <a class='  btn btn-primary ' style="margin-top:15px; margin-left:15px; "
               id="print_aramex_shipment">{l s='Print Label' mod='aramexshipping'} </a>
        {/if}
        {if  $shipped  eq true}
            <a class=' btn btn-primary ' style="margin-top:15px; margin-left:15px; "
               id="track_aramex_shipment"> {l s='Track Aramex Shipment' mod='aramexshipping'}</a>
        {/if}
    </div>


</div>
{if isset($note)}
    <div class="aramex_note">
        {foreach from=$note item=value}
            <span class="aramex_note_value">  {$value|escape:'html':'UTF-8'} </span>
            <br/>
        {/foreach}
    </div>
{/if}


<script>
    $(document).ready(function () {
        myObj = {
            printLabelUrl: '',
            wh: $(window).height(),
            ww: $(window).width(),
            shipperCountry: '',
            shipperCity: '',
            shipperZip: '',
            shipperState: '',
            recieverCountry: '',
            recieverCity: '',
            recieverZip: '',
            recieverState: '',
            openWindow: function (param1, param2) {
                $(param1).css({ 'visibility': 'hidden', 'display': 'block' });

                var h = $(param2).height();
                var w = $(param2).width();
                var wh = this.wh;
                var ww = this.ww;
                if (h >= wh) {
                    h = wh - 20;
                    $(param2).css({ 'height': (h - 30) });
                } else {
                    h = h + 30;
                }

                var t = wh - h;
                t = t / 2;
                var l = ww - w
                l = l / 2;
                $('.back-over').fadeIn(200);
                $(param1).css({
                    'visibility': 'visible',
                    'display': 'none',
                    'height': 'auto',
                    'top': 30 + 'px'
                }).fadeIn(500);

            },
            openCalc: function () {
                this.cropValues();
                this.openWindow('.cal-rate-part', '.cal-form');
            },
            defaultVal: function () {
                this.shipperCountry = $('aramex_shipment_shipper_country').value;
                this.shipperCity = $('aramex_shipment_shipper_city').value;
                this.shipperZip = $('aramex_shipment_shipper_postal').value;
                this.shipperState = $('aramex_shipment_shipper_state').value;

                this.recieverCountry = $('aramex_shipment_receiver_country').value;
                this.recieverCity = $('aramex_shipment_receiver_city').value;
                this.recieverZip = $('aramex_shipment_receiver_postal').value;
                this.recieverState = $('aramex_shipment_receiver_state').value;
            },
            cropValues: function () {
                this.defaultVal();
                var orginCountry = this.getId('origin_country');
                this.setSelectedValue(orginCountry, this.shipperCountry);
                $('origin_city').value = this.shipperCity;
                $('origin_zipcode').value = this.shipperZip;
                $('origin_state').value = this.shipperState;

                var desCountry = this.getId('destination_country');
                this.setSelectedValue(desCountry, this.recieverCountry);
                $('destination_city').value = this.recieverCity;
                $('destination_zipcode').value = this.recieverZip;
                $('destination_state').value = this.recieverState;
            },
            getId: function (id) {
                return document.getElementById(id);
            },
            setSelectedValue: function (selectObj, valueToSet) {
                for (var i = 0; i < selectObj.options.length; i++) {
                    if (selectObj.options[i].value == valueToSet) {
                        selectObj.options[i].selected = true;
                        return;
                    }
                }
            },
            openPickup: function () {
                this.defaultVal();
                var pickupCountry = this.getId('pickup_country');
                this.setSelectedValue(pickupCountry, this.shipperCountry);
                $('pickup_city').value = this.shipperCity;
                $('pickup_zip').value = this.shipperZip;
                $('pickup_state').value = this.shipperState;
                $('pickup_address').value = $('aramex_shipment_shipper_street').value;
                $('pickup_company').value = $('aramex_shipment_shipper_company').value;
                $('pickup_contact').value = $('aramex_shipment_shipper_name').value;
                $('pickup_email').value = $('aramex_shipment_shipper_email').value;
                this.openWindow('.schedule-pickup-part', '.pickup-form');
            },
            close: function () {
                $('.back-over').fadeOut(500);
                $('.cal-rate-part, .schedule-pickup-part').fadeOut(200);
                $('.rate-result').css('display', 'none');
                $('.pickup-result').css('display', 'none');
            },
            calcRate: function () {
                $('.aramex_loader').css('display', 'block');
                $('.rate-result').css('display', 'none');
                var link_to_calc = "{$link->getAdminLink('AdminRate',true)|escape:'html':'UTF-8'}";
                var parts = link_to_calc.split("=").pop();
                var link = "{$link->getAdminLink('AdminRate',false)|escape:'html':'UTF-8'}";
                $.ajax({
                    url: link+"&token="+parts,
                    type: "POST",
                    dataType: 'json',
                    data: $("#calc-rate-form").serialize()
                    ,
                    success: function (json) {
                        if (json.type == 'success') {
                            $(".result").html(json.html);
                            $('.aramex_loader').css('display', 'none');
                        } else {
                            var error = "<div class='error'>" + json.error + "</div>";
                            $(".result").html(error);
                            $('.aramex_loader').css('display', 'none');
                        }
                        $(".rate-result").show();
                    }

                });
            },
            track: function () {
                $('.aramex_loader').css('display', 'block');
                $('.track-result').css('display', "none");
                var link_to_calc = "{$link->getAdminLink('AdminTrack',true)|escape:'html':'UTF-8'}";
                var parts = link_to_calc.split("=").pop();
                var link = "{$link->getAdminLink('AdminTrack',false)|escape:'html':'UTF-8'}";
                $.ajax({
                    url: link+"&token="+parts,
                    type: "POST",
                    dataType: 'json',
                    data: $("#track-form").serialize(),
                    success: function (json) {
                        if (json.type == 'success') {
                            $(".result").html(json.html);
                            $('.aramex_loader').css('display', 'none');
                        } else {
                            var error = "<div class='error'>" + json.error + "</div>";
                            $(".result").html(error);
                            $('.aramex_loader').css('display', 'none');
                        }
                        $(".track-result").show();
                    }

                });
            },
            schedulePickup: function () {
                $('.pickup-result').css('display', 'none');
                $('.aramex_loader').css('display', 'block');
                var link_to_calc = "{$link->getAdminLink('AdminSchedule',true)|escape:'html':'UTF-8'}";
                var parts = link_to_calc.split("=").pop();
                var link = "{$link->getAdminLink('AdminSchedule',false)|escape:'html':'UTF-8'}";
                $.ajax({
                    url: link+"&token="+parts,
                    type: "POST",
                    dataType: 'json',
                    data: $("#pickup-form").serialize(),
                    success: function (json) {
                        if (json.type == 'success') {
                            $(".pickup-res").html(json.html);
                            $('.aramex_loader').css('display', 'none');
                        } else {
                            var error = "<div class='error'>" + json.error + "</div>";
                            $(".pickup-res").html(error);
                            $('.aramex_loader').css('display', 'none');
                        }
                        $(".pickup-result").show();
                    }
                });
            },
            ajax: function (formId, result1, result2) {
            }
        }
    });
</script>
<div id="aramex_overlay">
    <div id="aramex_shipment_creation">
        {if isset($smarty.session.aramex_errors) and $smarty.session.aramex_errors gt '0'}

            <div class="aramex_errors">
            {foreach from=$smarty.session.aramex_errors key=key item=error}
                {if $key eq 'error'}
                    {foreach from=$error item=value}
                        <span class="error">  {$value|escape:'html':'UTF-8'} </span>
                        <br/>
                    {/foreach}
                {else}
                    {foreach from=$error item=value}
                        <span class="success">  {$value|escape:'html':'UTF-8'} </span>
                        <br/>
                    {/foreach}
                {/if}
                </div>
            {/foreach}
        {/if}
        <form id="aramex_shipment" method="post"
              action="{$link->getAdminLink('AdminShipment')|escape:'html':'UTF-8'}"
              enctype="multipart/form-data">
            <input type="hidden" name="aramex_shipment_referer"
                   value="{$smarty.server.HTTP_HOST|escape:'html':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}"/>
            <input name="aramex_shipment_shipper_account" type="hidden" value="{$account|escape:'html':'UTF-8'}"/>
            <input name="aramex_shipment_shipper_account_pin" type="hidden" value="{$pin|escape:'html':'UTF-8'}"/>
            <input name="aramex_shipment_original_reference" type="hidden" value="{$order_id|escape:'html':'UTF-8'}"/>
            <FIELDSET class="aramex_shipment_creation_fieldset_big" id="aramex_shipment_creation_general_info">
                <legend>Billing Account</legend>
                <div id="general_details" class="aramex_shipment_creation_part">
                    <div class="text_short">
                        <label>Account</label>
                        <select class="aramex_all_options" name="aramex_shipment_shipper_account_show">
                            <option value="1">Normal Account</option>
                            {if $allowed_cod eq '1'}
                                <option value="2">COD Account</option>
                            {/if}
                        </select>
                        <div class="little_description">Taken from Aramex Global Settings</div>
                        <div class="aramex_clearer"></div>
                    </div>
                    <div id="aramex_shipment_creation_logo">
                    </div>
                    <div class="text_short">
                        <label>Payment</label>
                        <select class="aramex_all_options" name="aramex_shipment_info_billing_account"
                                id="aramex_shipment_info_billing_account_id">
                            <option
                                    value="1">
                                Shipper Account
                            </option>
                            <option
                                    value="2">
                                Consignee Account
                            </option>
                            <option
                                    value="3">
                                Third Party
                            </option>
                        </select>
                        <div id="aramex_shipment_info_service_type_div" style="display: none;"></div>
                    </div>
                    <div class="cal-rate-button" style="float:right;">
                        <button name="aramex_rate_calculate" type="button" id="aramex_rate_calculate"
                                class='button-primary'
                                onclick="myObj.openCalc();">Calculate Rate
                        </button>
                        <button name="aramex_schedule_pickup" type="button" id="aramex_schedule_pickup"
                                class='button-primary'
                                onclick="myObj.openPickup();">Schedule Pickup
                        </button>
                    </div>
            </FIELDSET>
            <div id="aramex_messages"></div>
            <!--  Shipper DetailsShipper Details -->
            <FIELDSET class="aramex_shipment_creation_fieldset aramex_shipment_creation_fieldset_left">
                <legend>Shipper Details</legend>
                <div id="shipper_details" class="aramex_shipment_creation_part">
                    <div class="text_short">
                        <label>Reference</label><input class="number" type="text"
                                                       name="aramex_shipment_shipper_reference"
                                                       value="{$order_id|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">

                        {if isset($smarty.session.form_data.aramex_shipment_shipper_name)}
                            {assign var = '_name'  value = $smarty.session.form_data.aramex_shipment_shipper_name }
                        {else}
                            {assign var = '_name' value = $name}
                        {/if}


                        <label>Name <span class="red">*</span></label><input type="text" class="required"
                                                                             id="aramex_shipment_shipper_name"
                                                                             name="aramex_shipment_shipper_name"
                                                                             value="{$_name|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_email' value = $smarty.session.form_data.aramex_shipment_shipper_email}
                        {else}
                            {assign var = '_email' value = $email}
                        {/if}
                        <label>Email <span class="red">*</span></label><input type="text" class="required email"
                                                                              id="aramex_shipment_shipper_email"
                                                                              name="aramex_shipment_shipper_email"
                                                                              value="{$_email|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_company' value = $smarty.session.form_data.aramex_shipment_shipper_company}
                        {else}
                            {assign var = '_company' value = $company}
                        {/if}
                        <label>Company</label><input type="text" id="aramex_shipment_shipper_company"
                                                     name="aramex_shipment_shipper_company"
                                                     value="{$_company|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_street' value = $smarty.session.form_data.aramex_shipment_shipper_street}
                        {else}
                            {assign var = '_street' value = $address}
                        {/if}
                        <label>Address <span class="red">*</span></label><textarea rows="4" class="required"
                                                                                   cols="26" type="text"
                                                                                   id="aramex_shipment_shipper_street"
                                                                                   name="aramex_shipment_shipper_street">{$_street|escape:'html':'UTF-8'}</textarea>
                    </div>
                    <div class="text_short">
                        <label>Country <span class="red">*</span></label>
                        <select class="aramex_countries validate-select" id="aramex_shipment_shipper_country"
                                name="aramex_shipment_shipper_country">
                            {if $session eq true}
                                {assign var = '_country' value = $smarty.session.form_data.aramex_shipment_shipper_country}
                            {else}
                                {assign var='_country' value = $country}
                            {/if}
                            {foreach from=$countries key=key item=value}
                                <option value="{$value|escape:'html':'UTF-8'}"
                                        {if isset($_country) && $_country == $value}
                                            {l s='selected="selected"' mod='aramexshipping'}
                                        {else}
                                            {l s='' mod='aramexshipping'}
                                        {/if}
                                >{$key|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_city' value = $smarty.session.form_data.aramex_shipment_shipper_city}
                        {else}
                            {assign var = '_city' value = $city}
                        {/if}
                        <label>City <span class="red no-display">*</span></label><input class="aramex_city"
                                                                                        autocomplete="off"
                                                                                        type="text"
                                                                                        id="aramex_shipment_shipper_city"
                                                                                        name="aramex_shipment_shipper_city"
                                                                                        value="{$_city|escape:'html':'UTF-8'}"/>

                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_postalcode' value = $smarty.session.form_data.aramex_shipment_shipper_postal}
                        {else}
                            {assign var = '_postalcode' value = $postalcode}
                        {/if}
                        <label>Postal Code <span class="red no-display">*</span></label><input class="" type="text"
                                                                                               id="aramex_shipment_shipper_postal"
                                                                                               name="aramex_shipment_shipper_postal"
                                                                                               value="{$_postalcode|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_state' value = $smarty.session.form_data.aramex_shipment_shipper_state}
                        {else}
                            {assign var = '_state' value = $state}
                        {/if}
                        <label>State</label><input type="text" id="aramex_shipment_shipper_state"
                                                   name="aramex_shipment_shipper_state"
                                                   value="{$_state|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_phone' value = $smarty.session.form_data.aramex_shipment_shipper_phone}
                        {else}
                            {assign var = '_phone' value = $phone}
                        {/if}
                        <label>Phone</label><input
                                class="required" type="text" id="aramex_shipment_shipper_phone"
                                name="aramex_shipment_shipper_phone" value="{$_phone|escape:'html':'UTF-8'}"/>
                    </div>
                </div>
            </FIELDSET>
            <!--  Receiver Details -->
            <FIELDSET class="aramex_shipment_creation_fieldset aramex_shipment_creation_fieldset_right">
                <legend>Receiver Details</legend>

                <div class="text_short">
                    <label>Reference</label><input class="number" type="text"
                                                   id="aramex_shipment_receiver_reference"
                                                   name="aramex_shipment_receiver_reference"
                                                   value="{$order_id|escape:'html':'UTF-8'}"/>
                </div>
                <div class="text_short">
                    {if isset($customer->firstname)}
                        {assign var = '_name' value = $customer->firstname}
                    {else}
                        {assign var = '_name' value = ''}
                    {/if}
                    {if isset($customer->lastname)}
                        {assign var = '_name_last' value = $customer->lastname}
                    {else}
                        {assign var = '_name_last' value = ''}
                    {/if}
                    {assign var = '_name' value = $_name|cat:" "|cat:$_name_last}

                    {if $session eq true}
                        {assign var = '_name' value = $smarty.session.form_data.aramex_shipment_receiver_name}
                    {else}
                        {assign var = '_name' value = $_name}
                    {/if}

                    <label>Name
                        <span class="red">*</span></label><input class="required" type="text"
                                                                 id="aramex_shipment_receiver_name"
                                                                 name="aramex_shipment_receiver_name"
                                                                 value="{$_name|escape:'html':'UTF-8'}"/>
                </div>
                <div class="text_short">
                    {if isset($customer->email)}
                        {assign var = '_email' value = $customer->email}
                    {else}
                        {assign var = '_email' value = ''}
                    {/if}

                    {if isset($smarty.session.form_data.aramex_shipment_receiver_email)}
                        {assign var = '_email' value = $customer->email}
                    {else}
                        {assign var = '_email' value = $_email}
                    {/if}
                    <label>Email <span class="red">*</span></label><input class="email required" type="text"
                                                                          id="aramex_shipment_receiver_email"
                                                                          name="aramex_shipment_receiver_email"
                                                                          value="{$_email|escape:'html':'UTF-8'}"/>
                </div>
                <div class="text_short">
                    {if empty($customer->company)}
                        {assign var = 'company_name' value = $customer->company}
                    {else}
                        {assign var = 'company_name' value = ''}
                    {/if}

                    {if empty($company_name)}
                        {assign var = 'company_name' value = {$customer->firstname|escape:'html':'UTF-8'|cat:" "|cat:$customer->lastname|escape:'html':'UTF-8'}}
                    {else}
                        {assign var = 'company_name' value = $company_name}
                    {/if}
                    {if $session eq true}
                        {assign var = 'company_name' value = $smarty.session.form_data.aramex_shipment_receiver_company}
                    {else}
                        {assign var = 'company_name' value = $company_name}
                    {/if}

                    <label>Company</label><input type="text" id="aramex_shipment_receiver_company"
                                                 name="aramex_shipment_receiver_company"
                                                 value="{$company_name|escape:'html':'UTF-8'}"/>
                </div>
                <div class="text_short">
                    {if $session eq true}
                        {assign var = 'address' value = $smarty.session.form_data.aramex_shipment_receiver_street}
                    {else}
                        {assign var = 'address' value = "`$address_reciver` `$address_reciver2`"}
                    {/if}
                    <label>Address <span class="red">*</span></label><textarea class="required" rows="4"
                                                                               cols="26" type="text"
                                                                               id="aramex_shipment_receiver_street"
                                                                               name="aramex_shipment_receiver_street">{$address|escape:'html':'UTF-8'}</textarea>
                </div>
                <div class="text_short">
                    <label>Country <span class="red">*</span></label>
                    {if $session eq true}
                        {assign var = '_country' value = $smarty.session.form_data.aramex_shipment_receiver_country}
                    {else}
                        {assign var = '_country' value = $reciverCountryCode}
                    {/if}
                    <select class="aramex_countries" id="aramex_shipment_receiver_country"
                            name="aramex_shipment_receiver_country">
                        {foreach from=$countries key=key item=value}
                            <option value="{$value|escape:'html':'UTF-8'}"
                                    {if isset($_country) && $_country == $value}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {else}
                                        {l s='' mod='aramexshipping'}
                                    {/if}
                            >{$key|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
                {if isset($reciverCity)}
                    {assign var = '_city' value = $reciverCity}
                {else}
                    {assign var = '_city' value = ''}
                {/if}

                {if $session eq true}
                    {assign var = '_city' value = $smarty.session.form_data.aramex_shipment_receiver_city}
                {else}
                    {assign var = '_city' value = $reciverCity}
                {/if}


                <div class="text_short">
                    <label>City
                        <span class="red no-display">*</span></label><input class="aramex_city" 
                                                                            type="text"
                                                                            id="aramex_shipment_receiver_city"
                                                                            name="aramex_shipment_receiver_city"
                                                                            value="{$_city|escape:'html':'UTF-8'}"/>
                    <div id="aramex_shipment_receiver_city_autocomplete" class="am_autocomplete"></div>
                </div>


                <div class="text_short">
                    {if isset($reciverPostcode)}
                        {assign var = '_postcode' value = $reciverPostcode}
                    {else}
                        {assign var = '_postcode' value = ''}
                    {/if}

                    {if $session eq true}
                        {assign var = '_postcode' value = $smarty.session.form_data.aramex_shipment_receiver_postal}
                    {else}
                        {assign var = '_postcode' value = $reciverPostcode}
                    {/if}
                    <label>Postal Code <span class="red no-display">*</span></label><input type="text" class=""
                                                                                           id="aramex_shipment_receiver_postal"
                                                                                           name="aramex_shipment_receiver_postal"
                                                                                           value="{$_postcode|escape:'html':'UTF-8'}"/>
                </div>
                {if $session eq true}
                    {assign var = '_state' value = $smarty.session.form_data.aramex_shipment_receiver_state}
                {else}
                    {assign var = '_state' value = ''}
                {/if}
                <div class="text_short">
                    <label>State</label><input type="text" id="aramex_shipment_receiver_state"
                                               name="aramex_shipment_receiver_state"
                                               value="{$_state|escape:'html':'UTF-8'}"/>
                </div>
                <div class="text_short">

                    {if isset($reciverPhone)}
                        {assign var = '_phone' value = $reciverPhone}
                    {else}
                        {assign var = '_phone' value = ''}
                    {/if}


                    {if $session eq true}
                        {assign var = '_phone' value = $smarty.session.form_data.aramex_shipment_receiver_phone}
                    {else}
                        {assign var = '_phone' value = $reciverPhone}
                    {/if}

                    <label>Phone</label><input class="required" type="text" id="aramex_shipment_receiver_phone"
                                               name="aramex_shipment_receiver_phone"
                                               value="{$_phone|escape:'html':'UTF-8'}"/>
                </div>
            </FIELDSET>
            <!-- Shipment Information -->
            <div class="aramex_clearer"></div>
            <FIELDSET class="aramex_shipment_creation_fieldset_big">
                <legend>Shipment Information</legend>
                <div id="shipment_infromation" class="aramex_shipment_creation_part">
                    <div class="text_short">
                        <label>Total weight:</label>
                        {if $session eq true}
                            {assign var = 'totalWeight' value = $smarty.session.form_data.order_weight}
                        {else}
                            {assign var = 'totalWeight' value = $totalWeight}
                        {/if}
                        <input type="text" name="order_weight" value="{$totalWeight|escape:'html':'UTF-8'}"
                               class="fl width-60 mar-right-10"/>
                        <select name="weight_unit" class="fl width-60" style="height:24px;padding:0px;">
                            <option value="kg"
                                    {if $unit eq 'kg' }
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {else}
                                        {l s='' mod='aramexshipping'}
                                    {/if}
                            >
                                {l s='kg' mod='aramexshipping'}
                            </option>
                            <option value="lbs"
                                    {if $unit eq 'lbs' }
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {else}
                                        {l s='' mod='aramexshipping'}
                                    {/if}
                            >
                                {l s='lbs' mod='aramexshipping'}
                            </option>
                        </select>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = 'order_id' value = $smarty.session.form_data.aramex_shipment_info_reference}
                        {else}
                            {assign var = 'order_id' value = $order_id}
                        {/if}

                        <label>Reference</label><input type="text" id="aramex_shipment_info_reference"
                                                       name="aramex_shipment_info_reference"
                                                       value="{$order_id|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        <label>Product Group</label>
                        {if isset($reciverCountryCode)}
                            {assign var = '_country' value = $reciverCountryCode}
                        {else}
                            {assign var = '_country' value = ''}
                        {/if}


                        {if $_country eq $country }
                            {assign var = 'checkCountry' value = true}
                        {else}
                            {assign var = 'checkCountry' value = false}
                        {/if}


                        {if isset($smarty.session.form_data.aramex_shipment_info_product_group) and $smarty.session.form_data.aramex_shipment_info_product_group eq "DOM"}
                            {assign var = 'checkCountry' value = true}
                        {/if}


                        {if isset($smarty.session.form_data.aramex_shipment_info_product_group) and $smarty.session.form_data.aramex_shipment_info_product_group eq "EXP" }
                            {assign var = 'checkCountry' value = false}
                        {/if}


                        <select class="aramex_all_options" id="aramex_shipment_info_product_group"
                                name="aramex_shipment_info_product_group">
                            <option
                                    {if $checkCountry eq true}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {else}
                                        {l s='' mod='aramexshipping'}
                                    {/if}
                                    value="DOM">Domestic
                            </option>
                            <option
                                    {if $checkCountry eq false}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {else}
                                        {l s='' mod='aramexshipping'}
                                    {/if}
                                    value="EXP">International Express
                            </option>
                        </select>
                        <div id="aramex_shipment_info_product_group_div" style="display: none;"></div>
                    </div>
                    <div class="text_short">
                        <label>Service Type</label>
                        <select class="aramex_all_options" id="aramex_shipment_info_product_type"
                                name="aramex_shipment_info_product_type">

                            {if $session eq true}
                                {assign var = 'dom' value = $smarty.session.form_data.aramex_shipment_info_product_type}
                            {else}
                                {assign var = 'dom' value = $allowed_method}
                            {/if}

                            {if $session eq true}
                                {assign var = 'exp' value = $smarty.session.form_data.aramex_shipment_info_product_type}
                            {else}
                                {assign var = 'exp' value = $allowed_method}
                            {/if}
                            {if count($allowed_domestic_methods) > 0}
                                {foreach from=$allowed_domestic_methods key=key item=value}
                                    {if $dom eq $key}
                                        {assign var = 'selected_str' value = 'selected="selected"'}
                                    {else}
                                        {assign var = 'selected_str' value = ""}
                                    {/if}
                                    <option
                                            {$selected_str|escape:'html':'UTF-8'} value="{$key|escape:'html':'UTF-8'}"
                                                            id="{$key|escape:'html':'UTF-8'}"
                                                            class="DOM">{$value|escape:'html':'UTF-8'}</option>
                                {/foreach}
                                {if  count($allowed_international_methods) > 0}
                                    {foreach from=$allowed_international_methods key=key item=value}
                                        {assign var="selected_str" value=''}
                                        {if $exp eq $key}
                                            {assign var="selected_str" value='selected="selected"'}
                                        {/if}
                                        <option
                                                {$selected_str|escape:'html':'UTF-8'} value="{$key|escape:'html':'UTF-8'}"
                                                                id="{$key|escape:'html':'UTF-8'}"
                                                                class="EXP">{$value|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                {/if}
                            {/if}
                        </select>
                        <div id="aramex_shipment_info_service_type_div" style="display: none;"></div>
                    </div>
                    <div class="text_short">
                        <label>Additional Services</label>
                        {assign var = '_type' value =  $allowed_domestic_additional_services}
                        {if isset($smarty.session.form_data.aramex_shipment_info_service_type)}
                            {assign var = '_type' value = $smarty.session.form_data.aramex_shipment_info_service_type}
                        {else}
                            {assign var = '_type' value = $_type}
                        {/if}
                        <select class="aramex_all_options" id="aramex_shipment_info_service_type"
                                name="aramex_shipment_info_service_type[]" multiple
                                style="height:120px;"
                        >
                            <option value=""></option>

                            {if count($allowed_domestic_additional_services) > 0}
                                {foreach from=$allowed_domestic_additional_services key=key item=value }
                                    <option
                                            {if is_array($_type)}
                                                {if in_array($key, $_type)}
                                                    {l s='selected="selected"' mod='aramexshipping'}
                                                {else}
                                                    {l s='' mod='aramexshipping'}
                                                {/if}
                                            {else}
                                                {if $_type eq $key}
                                                    {l s='selected="selected"' mod='aramexshipping'}
                                                {else}
                                                    {l s='' mod='aramexshipping'}
                                                {/if}
                                            {/if}
                                            value="{$key|escape:'html':'UTF-8'}"
                                            id="dom_as_{$key|escape:'html':'UTF-8'}"
                                            class="DOM local">{$value|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            {/if}

                            {if count($allowed_international_additional_services) > 0}
                                {foreach from=$allowed_international_additional_services key=key item=value }
                                    <option
                                            {if is_array($_type)}
                                                {if in_array($key, $_type)}
                                                    {l s='selected="selected"' mod='aramexshipping'}
                                                {else}
                                                    {l s='' mod='aramexshipping'}
                                                {/if}
                                            {else}
                                                {if $_type eq $key}
                                                    {l s='selected="selected"' mod='aramexshipping'}
                                                {else}
                                                    {l s='' mod='aramexshipping'}
                                                {/if}
                                            {/if}


                                            value="{$key|escape:'html':'UTF-8'}"
                                            id="exp_as_{$key|escape:'html':'UTF-8'}"
                                            class="non-local EXP">{$value|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                    <div class="text_short">
                        <label>Payment Type</label>
                        {if $session eq true}
                            {assign var = '_type' value = $smarty.session.form_data.aramex_shipment_info_payment_type}
                        {else}
                            {assign var = '_type' value = ""}
                        {/if}

                        <select class="aramex_all_options" id="aramex_shipment_info_payment_type"
                                name="aramex_shipment_info_payment_type">

                            <option value="P"
                                    {if $_type eq 'P'}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {/if}>Prepaid
                            </option>
                            <option value="C"
                                    {if $_type == 'C'}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {/if}>Collect
                            </option>
                            <option value="3"
                                    {if $_type eq '3'}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {/if}
                            >Third Party
                            </option>
                        </select>
                        <div id="aramex_shipment_info_service_type_div" style="display: none;"></div>
                    </div>
                    <div class="text_short">
                        <label>Payment Option</label>
                        <select class="" id="aramex_shipment_info_payment_option"
                                name="aramex_shipment_info_payment_option">

                            {if $session eq true}
                                {assign var = '_option' value = $smarty.session.form_data.aramex_shipment_info_payment_option}
                            {else}
                                {assign var = '_option' value = ""}
                            {/if}

                            <option value=""></option>
                            <option id="ASCC" value="ASCC" style="display: none;">Needs Shipper Account Number to be
                                filled
                            </option>
                            <option id="ARCC" value="ARCC" style="display: none;">Needs Consignee Account Number to
                                be filled
                            </option>

                            <option id="CASH" value="CASH"

                                    {if $_option eq 'CASH'}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {/if}
                            >Cash
                            </option>
                            <option id="ACCT" value="ACCT"

                                    {if $_option eq 'ACCT'}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {/if}
                            >Account
                            </option>
                            <option id="PPST" value="PPST"
                                    {if $_option eq 'PPST'}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {/if}
                            >Prepaid Stock
                            </option>
                            <option id="CRDT" value="CRDT"

                                    {if $_option eq 'CRDT'}
                                        {l s='selected="selected"' mod='aramexshipping'}
                                    {/if}
                            >Credit
                            </option>
                        </select>

                    </div>
                    <div class="text_short">

                        {assign var = '_amount' value = $total_paid }
                        {if $session eq true}
                            {assign var = '_amount' value = $smarty.session.form_data.aramex_shipment_info_cod_amount}
                        {else}
                            {assign var = '_amount' value = $_amount}
                        {/if}

                        <label>COD Amount</label><input class="" type="text" id="aramex_shipment_info_cod_amount"
                                                        name="aramex_shipment_info_cod_amount"
                                                        value="{$_amount|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_amount' value = $smarty.session.form_data.aramex_shipment_info_custom_amount}
                        {else}
                            {assign var = '_amount' value = ''}
                        {/if}

                        <label>Custom Amount</label><input class="" type="text"
                                                           id="aramex_shipment_info_custom_amount"
                                                           name="aramex_shipment_info_custom_amount"
                                                           value="{$_amount|escape:'html':'UTF-8'}"/>
                    </div>

                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_code' value = $smarty.session.form_data.aramex_shipment_currency_code}
                        {else}
                            {assign var = '_code' value =  $currencyCode}
                        {/if}
                        <label>COD Currency</label><input type="text" class="" id="aramex_shipment_currency_code"
                                                          name="aramex_shipment_currency_code"
                                                          value="{$_code|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_code' value = $smarty.session.form_data.aramex_shipment_currency_code_custom}
                        {else}
                            {assign var = '_code' value = ""}
                        {/if}

                        <label>Custom Currency</label><input type="text" class=""
                                                             id="aramex_shipment_currency_code_custom"
                                                             name="aramex_shipment_currency_code_custom"
                                                             value="{$_code|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_comment' value = $smarty.session.form_data.aramex_shipment_info_comment}
                        {else}
                            {assign var = '_comment' value = ""}
                        {/if}

                        <label>Comment</label><textarea rows="4"
                                                        cols="
                                                        {l s='29' mod='aramexshipping'}"

                                                        type="text" id="aramex_shipment_info_comment"
                                                        name="aramex_shipment_info_comment">{$_comment|escape:'html':'UTF-8'}</textarea>
                    </div>

                    <div class="text_short">
                        {if $session eq true}
                            {assign var = '_foreignhawb' value = $smarty.session.form_data.aramex_shipment_info_foreignhawb}
                        {else}
                            {assign var = '_foreignhawb' value = ""}
                        {/if}
                        <label>Foreign Shipment No</label><input class="" type="text"
                                                                 id="aramex_shipment_info_foreignhawb"
                                                                 name="aramex_shipment_info_foreignhawb"
                                                                 value="{$_foreignhawb|escape:'html':'UTF-8'}"/>
                    </div>
                    <div class="text_short">
                        <label for="file1">Filename 1:</label>

                        <div id="file1_div" style="float: left;width: 145px;">
                            <input type="file" name="file1" id="file1" size="7">
                        </div>
                        <div style="float: right;">
                            <input type="button" name="filereset" id="filereset" value="Reset"
                                   style="width: 60px;height: 24px;"/>
                        </div>
                    </div>
                    <div class="text_short">
                        <label for="file2">Filename 2:</label>

                        <div id="file2_div" style="float: left;width: 145px;">
                            <input type="file" name="file2" id="file2" size="7">
                        </div>
                        <div style="float: right;">
                            <input type="button" name="file2reset" id="file2reset" value="Reset"
                                   style="width: 60px;height: 24px;"/>
                        </div>
                    </div>
                    <div class="text_short">
                        <label for="file">Filename 3:</label>
                        <div id="file3_div" style="float: left;width: 145px;">
                            <input type="file" name="file3" id="file3" size="7">
                        </div>
                        <div style="float: right;">
                            <input type="button" name="file3reset" id="file3reset" value="Reset"
                                   style="width: 60px;height: 24px;"/>
                        </div>
                    </div>

                    <div class="text_short">
                        <label>Description</label>
                        <textarea rows="4" cols="31" type="text"
                                  id="aramex_shipment_description"
                                  name="aramex_shipment_description"
                                  >
                                                                {foreach from=$products item=item}
                                                                    {$item['product_id']|escape:'html':'UTF-8'|cat:' - '|cat:$item['product_name']|escape:'html':'UTF-8'|strip}
                                                                {/foreach}
                                                            </textarea>
                        <div id="aramex_shipment_description_div" style="    float: left;
                                                                 font-size: 11px;
                                                                 margin-bottom: 5px;
                                                                 margin-top: 2px;
                                                                 width: 202px;">

                        </div>
                    </div>
                    <div class="text_short">
                        <label>Items Price</label><input type="text" id="aramex_shipment_info_items_subtotal"
                                                         name="aramex_shipment_info_items_subtotal"
                                                         disabled="disabled" style="width: 165px; float: left;"
                                                         value="{$total_paid|escape:'html':'UTF-8'}"/>

                        <div
                                style="float: left; padding-left: 5px;">{$currencyCode|escape:'html':'UTF-8'}</div>
                    </div>
                    <div class="text_short">
                        {if $session eq true}
                            {assign var = 'aramex_number_pieces' value = $smarty.session.form_data.number_pieces}
                        {else}
                            {assign var = 'aramex_number_pieces' value = "1"}
                        {/if}
                        <label>Number of Pieces</label><input type="text" name="number_pieces"
                                                              value="{$aramex_number_pieces|escape:'html':'UTF-8'}"
                                                              style="width: 165px; float: left;"/>
                        <div style="float: left; padding-left: 5px;"></div>
                    </div>
                </div>
                <div id="shipment_infromation2" class="aramex_shipment_creation_part">
                    <div class="text_short" id="aramex_shipment_info_items">
                        <div>
                            <div style="margin-bottom: -15px;">Items not shipped yet</div>
                            <br/>
                            <table id="aramex_items_table">
                                <tr>
                                    <th class="aramex_item_options">Action</th>
                                    <th class="aramex_item_name">Name</th>
                                    <th class="aramex_item_qty">Qty</th>
                                </tr>
                                {assign var = 'qty' value = 0}
                                {foreach from = $products item=item}
                                    <tr id="item{$item['product_id']|escape:'html':'UTF-8'}" class="aramex_item_tobe_shipped">
                                        <td></td>
                                        <td class="aramex_item_name">
                                                <span
                                                        title="{$item['product_name']|escape:'html':'UTF-8'}">{substr($item['product_name'], 0, 21)|escape:'html':'UTF-8'}
                                                    ...</span>
                                            <input type="hidden"
                                                   id="aramex_items_{$item['product_id']|escape:'html':'UTF-8'}"
                                                   name="aramex_items[{$item['product_id']|escape:'html':'UTF-8'}]"
                                                   value="{$item['product_quantity']|escape:'html':'UTF-8'}"/>
                                        </td>
                                        <td class="aramex_item_qty">
                                            <input class="aramex_input_items_qty" type="text"
                                                   name="p_{$item['product_id']|escape:'html':'UTF-8'}"
                                                   value="{$item['product_quantity']|escape:'html':'UTF-8'}"/>
                                            <input type="hidden"
                                                   id="aramex_items_base_price_{$item['product_id']|escape:'html':'UTF-8'}"
                                                   name="aramex_items_base_price_{$item['product_id']|escape:'html':'UTF-8'}"
                                                   value="{$item['weight']|escape:'html':'UTF-8'}"/>
                                            <input type="hidden"
                                                   id="aramex_items_base_weight_{$item['product_id']|escape:'html':'UTF-8'}"
                                                   name="aramex_items_base_weight_{$item['product_id']|escape:'html':'UTF-8'}"
                                                   value="{$item['weight']|escape:'html':'UTF-8'}"/>
                                            <input type="hidden"
                                                   id="aramex_items_total_{$item['product_id']|escape:'html':'UTF-8'}"
                                                   name="aramex_items_total_{$item['product_id']|escape:'html':'UTF-8'}"
                                                   value="{$item['product_quantity']|escape:'html':'UTF-8'}"/>
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                    </div>
                </div>
                <div class="aramex_clearer"></div>
            </FIELDSET>
            <div class="aramex_clearer"></div>
            <div style="float: right;margin-bottom: 20px;margin-top: -11px;">
                {if $aramex_return_button}
                    <input name="aramex_return_shipment_creation_date" type="hidden" value="return"/>
                    <button id="aramex_return_shipment_creation_submit_id" type="submit"
                            name="aramex_return_shipment_creation_submit_id" class="button-primary">Return Order
                    </button>
                {else}
                    <div style="width: 100%;  padding-top:10px; overflow:hidden;">
                        <div style="float: right;font-size: 11px;margin-bottom: 10px;width: 184px;">
                            <input
                                    style="float: left; width: auto; height:16px; display:block;" type="checkbox"
                                    name="aramex_email_customer" value="yes"/>
                            <span style="float: left; margin-left: 2px; margin-top: 2px;">Notify customer by email</span>
                        </div>
                    </div>
                    <div class="aramex_clearer"></div>
                    <input name="aramex_return_shipment_creation_date" type="hidden" value="create"/>
                    <button id="aramex_shipment_creation_submit_id" type="submit"
                            name="aramex_shipment_creation_submit" class="button-primary">Create Shipment
                    </button>
                {/if}
                <button id="aramex_close" class="button-primary" type="button">Close</button>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        var aramex_shipment_shipper_name = document.getElementById('aramex_shipment_shipper_name').value;
        var aramex_shipment_shipper_email = document.getElementById('aramex_shipment_shipper_email').value;
        var aramex_shipment_shipper_company = document.getElementById('aramex_shipment_shipper_company').value;
        var aramex_shipment_shipper_street = document.getElementById('aramex_shipment_shipper_street').value;
        var aramex_shipment_shipper_country = document.getElementById('aramex_shipment_shipper_country').value;
        var aramex_shipment_shipper_city = document.getElementById('aramex_shipment_shipper_city').value;
        var aramex_shipment_shipper_postal = document.getElementById('aramex_shipment_shipper_postal').value;
        var aramex_shipment_shipper_state = document.getElementById('aramex_shipment_shipper_state').value;
        var aramex_shipment_shipper_phone = document.getElementById('aramex_shipment_shipper_phone').value;
        var aramex_shipment_receiver_name = document.getElementById('aramex_shipment_receiver_name').value;
        var aramex_shipment_receiver_email = document.getElementById('aramex_shipment_receiver_email').value;
        var aramex_shipment_receiver_company = document.getElementById('aramex_shipment_receiver_company').value;
        var aramex_shipment_receiver_street = document.getElementById('aramex_shipment_receiver_street').value;
        var aramex_shipment_receiver_country = document.getElementById('aramex_shipment_receiver_country').value;
        var aramex_shipment_receiver_city = document.getElementById('aramex_shipment_receiver_city').value;
        var aramex_shipment_receiver_postal = document.getElementById('aramex_shipment_receiver_postal').value;
        var aramex_shipment_receiver_state = document.getElementById('aramex_shipment_receiver_state').value;
        var aramex_shipment_receiver_phone = document.getElementById('aramex_shipment_receiver_phone').value;
        jQuery(document).ready(function ($) {
            $("#aramex_shipment_info_billing_account_id").change(function () {
                resetShipperDetail(this);
            });
        });

        function resetShipperDetail(el) {
            //alert(el.value);
            var elValue = el.value;
            var flag = 0;
            if (elValue == 2) {
                document.getElementById('aramex_shipment_shipper_name').value = aramex_shipment_receiver_name;
                document.getElementById('aramex_shipment_shipper_email').value = aramex_shipment_receiver_email;
                document.getElementById('aramex_shipment_shipper_company').value = aramex_shipment_receiver_company;
                document.getElementById('aramex_shipment_shipper_street').value = aramex_shipment_receiver_street;
                document.getElementById('aramex_shipment_shipper_country').value = aramex_shipment_receiver_country;
                document.getElementById('aramex_shipment_shipper_city').value = aramex_shipment_receiver_city;
                document.getElementById('aramex_shipment_shipper_postal').value = aramex_shipment_receiver_postal;
                document.getElementById('aramex_shipment_shipper_state').value = aramex_shipment_receiver_state;
                document.getElementById('aramex_shipment_shipper_phone').value = aramex_shipment_receiver_phone;
                document.getElementById('aramex_shipment_receiver_name').value = aramex_shipment_shipper_name;
                document.getElementById('aramex_shipment_receiver_email').value = aramex_shipment_shipper_email;
                document.getElementById('aramex_shipment_receiver_company').value = aramex_shipment_shipper_company;
                document.getElementById('aramex_shipment_receiver_street').value = aramex_shipment_shipper_street;
                document.getElementById('aramex_shipment_receiver_country').value = aramex_shipment_shipper_country;
                document.getElementById('aramex_shipment_receiver_city').value = aramex_shipment_shipper_city;
                document.getElementById('aramex_shipment_receiver_postal').value = aramex_shipment_shipper_postal;
                document.getElementById('aramex_shipment_receiver_state').value = aramex_shipment_shipper_state;
                document.getElementById('aramex_shipment_receiver_phone').value = aramex_shipment_shipper_phone;
                flag = 1;
            } else if (elValue == 3) {
                document.getElementById('aramex_shipment_shipper_name').value = "";
                document.getElementById('aramex_shipment_shipper_email').value = "";
                document.getElementById('aramex_shipment_shipper_company').value = "";
                document.getElementById('aramex_shipment_shipper_street').value = "";
                document.getElementById('aramex_shipment_shipper_country').value = "";
                document.getElementById('aramex_shipment_shipper_city').value = "";
                document.getElementById('aramex_shipment_shipper_postal').value = "";
                document.getElementById('aramex_shipment_shipper_state').value = "";
                document.getElementById('aramex_shipment_shipper_phone').value = "";
                document.getElementById('aramex_shipment_info_payment_type').value = '3';
                document.getElementById('ASCC').style.display = 'block';
                document.getElementById('ARCC').style.display = 'block';
                document.getElementById('CASH').style.display = 'none';
                document.getElementById('ACCT').style.display = 'none';
                document.getElementById('PPST').style.display = 'none';
                document.getElementById('CRDT').style.display = 'none';
                $('#aramex_shipment_info_payment_option').val("");
                flag = 2;
            } else {
                if (flag = 1) {
                    document.getElementById('aramex_shipment_receiver_name').value = aramex_shipment_receiver_name;
                    document.getElementById('aramex_shipment_receiver_email').value = aramex_shipment_receiver_email;
                    document.getElementById('aramex_shipment_receiver_company').value = aramex_shipment_receiver_company;
                    document.getElementById('aramex_shipment_receiver_street').value = aramex_shipment_receiver_street;
                    document.getElementById('aramex_shipment_receiver_country').value = aramex_shipment_receiver_country;
                    document.getElementById('aramex_shipment_receiver_city').value = aramex_shipment_receiver_city;
                    document.getElementById('aramex_shipment_receiver_postal').value = aramex_shipment_receiver_postal;
                    document.getElementById('aramex_shipment_receiver_state').value = aramex_shipment_receiver_state;
                    document.getElementById('aramex_shipment_receiver_phone').value = aramex_shipment_receiver_phone;
                    document.getElementById('aramex_shipment_shipper_name').value = aramex_shipment_shipper_name;
                    document.getElementById('aramex_shipment_shipper_email').value = aramex_shipment_shipper_email;
                    document.getElementById('aramex_shipment_shipper_company').value = aramex_shipment_shipper_company;
                    document.getElementById('aramex_shipment_shipper_street').value = aramex_shipment_shipper_street;
                    document.getElementById('aramex_shipment_shipper_country').value = aramex_shipment_shipper_country;
                    document.getElementById('aramex_shipment_shipper_city').value = aramex_shipment_shipper_city;
                    document.getElementById('aramex_shipment_shipper_postal').value = aramex_shipment_shipper_postal;
                    document.getElementById('aramex_shipment_shipper_state').value = aramex_shipment_shipper_state;
                    document.getElementById('aramex_shipment_shipper_phone').value = aramex_shipment_shipper_phone;
                    document.getElementById('aramex_shipment_info_payment_type').value = 'P';
                    document.getElementById('ASCC').style.display = 'none';
                    document.getElementById('ARCC').style.display = 'none';
                    document.getElementById('CASH').style.display = 'block';
                    document.getElementById('ACCT').style.display = 'block';
                    document.getElementById('PPST').style.display = 'block';
                    document.getElementById('CRDT').style.display = 'block';
                    $('#aramex_shipment_info_payment_option').val("");
                } else if (flag = 2) {
                    document.getElementById('aramex_shipment_shipper_name').value = aramex_shipment_shipper_name;
                    document.getElementById('aramex_shipment_shipper_email').value = aramex_shipment_shipper_email;
                    document.getElementById('aramex_shipment_shipper_company').value = aramex_shipment_shipper_company;
                    document.getElementById('aramex_shipment_shipper_street').value = aramex_shipment_shipper_street;
                    document.getElementById('aramex_shipment_shipper_country').value = aramex_shipment_shipper_country;
                    document.getElementById('aramex_shipment_shipper_city').value = aramex_shipment_shipper_city;
                    document.getElementById('aramex_shipment_shipper_postal').value = aramex_shipment_shipper_postal;
                    document.getElementById('aramex_shipment_shipper_state').value = aramex_shipment_shipper_state;
                    document.getElementById('aramex_shipment_shipper_phone').value = aramex_shipment_shipper_phone;
                    document.getElementById('aramex_shipment_receiver_name').value = aramex_shipment_receiver_name;
                    document.getElementById('aramex_shipment_receiver_email').value = aramex_shipment_receiver_email;
                    document.getElementById('aramex_shipment_receiver_company').value = aramex_shipment_receiver_company;
                    document.getElementById('aramex_shipment_receiver_street').value = aramex_shipment_receiver_street;
                    document.getElementById('aramex_shipment_receiver_country').value = aramex_shipment_receiver_country;
                    document.getElementById('aramex_shipment_receiver_city').value = aramex_shipment_receiver_city;
                    document.getElementById('aramex_shipment_receiver_postal').value = aramex_shipment_receiver_postal;
                    document.getElementById('aramex_shipment_receiver_state').value = aramex_shipment_receiver_state;
                    document.getElementById('aramex_shipment_receiver_phone').value = aramex_shipment_receiver_phone;
                    document.getElementById('aramex_shipment_info_payment_type').value = 'P';
                    document.getElementById('ASCC').style.display = 'none';
                    document.getElementById('ARCC').style.display = 'none';
                    document.getElementById('CASH').style.display = 'block';
                    document.getElementById('ACCT').style.display = 'block';
                    document.getElementById('PPST').style.display = 'block';
                    document.getElementById('CRDT').style.display = 'block';
                    $('#aramex_shipment_info_payment_option').val("");
                }
                flag = 0;
            }
            /* hot fix  P.R */
            $(".aramex_countries").trigger('change');
        }

        jQuery('#aramex_shipment_info_payment_type').change(function ($) {
            //alert('Hello');
            if ($('#aramex_shipment_info_payment_type').val() == "P") {
                document.getElementById('ASCC').style.display = 'none';
                document.getElementById('ARCC').style.display = 'none';
                document.getElementById('CASH').style.display = 'block';
                document.getElementById('ACCT').style.display = 'block';
                document.getElementById('PPST').style.display = 'block';
                document.getElementById('CRDT').style.display = 'block';
                $('#aramex_shipment_info_payment_option').val("");
            } else {
                document.getElementById('ASCC').style.display = 'block';
                document.getElementById('ARCC').style.display = 'block';
                document.getElementById('CASH').style.display = 'none';
                document.getElementById('ACCT').style.display = 'none';
                document.getElementById('PPST').style.display = 'none';
                document.getElementById('CRDT').style.display = 'none';
                $('#aramex_shipment_info_payment_option').val("");
            }
        });
        {assign var = 'currentUrl'  value = $smarty.server.HTTP_HOST|cat: $smarty.server.REQUEST_URI}
        {if strpos($currentUrl, "aramexpopup/show")}
        aramexpop();

        function aramexpop() {
            $("#aramex_overlay").css("display", "block");
            $("#aramex_shipment").css("display", "block");
            $("#aramex_shipment_creation").fadeIn(1000);
        }
        {/if}

        $("input[name=aramex_shipment_info_shipping_charges]").change(function () {
            var cod_value = parseFloat($("input[name=aramex_shipment_info_shipping_charges]").val()) + parseFloat($("input[name=aramex_shipment_info_items_subtotal]").val());
            $("input[name=aramex_shipment_info_cod_value]").val(cod_value);
        });
        $("#aramex_shipment_info_product_group").change(function () {

            if ($("select[name=aramex_shipment_info_product_group]").val() == 'EXP') {
                $("select[name=aramex_shipment_info_additional_services] option:selected").removeAttr("selected");
                $("select[name=aramex_shipment_info_additional_services] .express_service").attr("selected", "selected");
                $("#aramex_shipment_info_product_type option").hide();
            } else if ($("select[name=aramex_shipment_info_product_group]").val() == 'DOM') {
                $("select[name=aramex_shipment_info_additional_services] option:selected").removeAttr("selected");
                $("select[name=aramex_shipment_info_additional_services] .domestic_service").attr("selected", "selected");
                $("#aramex_shipment_info_product_type option").hide();
            }
            $("#aramex_shipment_info_service_type_div").html($("select[name=aramex_shipment_info_service_type] option:selected").text());
            $("#aramex_shipment_info_additional_services_div").html($("select[name=aramex_shipment_info_additional_services] option:selected").text());
        });
        $("#aramex_shipment_info_product_group_div").html($("select[name=aramex_shipment_info_product_group] option:selected").text());
        $("#aramex_shipment_info_service_type_div").html($("select[name=aramex_shipment_info_service_type] option:selected").text());
        $("#aramex_shipment_info_additional_services_div").html($("select[name=aramex_shipment_info_additional_services] option:selected").text());

        $(document).ready(function () {
            if (($('#aramex_messages').html() != "") && ($('.error-msg'))) {
                $("#aramex_overlay").css("display", "block");
                $("#aramex_shipment_creation").fadeIn(1000);
            }

            $(function () {
                $("#aramex_shipment_info_pickup_date").datepicker({ dateFormat: "yy-mm-dd" });
                $("#aramex_shipment_info_ready_time").datepicker({ dateFormat: "yy-mm-dd" });
                $("#aramex_shipment_info_last_pickup_time").datepicker({ dateFormat: "yy-mm-dd" });
                $("#aramex_shipment_info_closing_time").datepicker({ dateFormat: "yy-mm-dd" });
            });
            $('#filereset').click(function () {
                $("#file1_div").html($("#file1_div").html());
            });
            $('#file2reset').click(function () {
                $("#file2_div").html($("#file2_div").html());
            });
            $('#file3reset').click(function () {
                $("#file3_div").html($("#file3_div").html());
            });
            $("#aramex_shipment_info_product_type").chained("#aramex_shipment_info_product_group");
            $("#aramex_shipment_info_service_type").chained("#aramex_shipment_info_product_group");
            $("#aramex_return_shipment_creation_submit_id").click(function () {
                $('.loading-mask').css('display', 'block');
            });
            $("#aramex_shipment_creation_submit_id").click(function () {
                $('.loading-mask').css('display', 'block');
            });
        });
    });
</script>

<style>
    .ui-front {
        z-index: 10000000;
    }

    .ui-autocomplete {
        max-height: 200px;
        overflow-y: auto;
        /* prevent horizontal scrollbar */
        overflow-x: hidden;
        /* add padding to account for vertical scrollbar */
    }
</style>
<script type="text/javascript">
    $(document).ready(function () {
        {if $aramex_return_button eq true}
        $("#aramex_shipment_info_billing_account_id").val(2);
        $("#aramex_shipment_info_billing_account_id").trigger('change');
        {/if}


        /* billing_aramex_cities and  shipping_aramex_cities */
        var type = '.aramex_shipment_creation_fieldset_left';
        setAutocomplate(type);
        var type = '.aramex_shipment_creation_fieldset_right';
        setAutocomplate(type);
        var type = '.aramex_top';
        setAutocomplate(type);
        var type = '.aramex_bottom';
        setAutocomplate(type);
        var type = '.schedule-pickup-part';
        setAutocomplate(type);

        function setAutocomplate(type) {
            var shippingAramexCitiesObj;
            var shipping_aramex_cities_temp;
            var billing_aramex_cities = '';
            var shipping_aramex_cities = billing_aramex_cities;
            /* set HTML blocks */
            shipping_aramex_cities_temp = shipping_aramex_cities;
            /* get Aramex sities */
          /*  shippingAramexCitiesObj = AutoSearchControls(type, shipping_aramex_cities); */
            $(type).find(".aramex_countries").change(function () {
                getAllCitiesJson(type, shippingAramexCitiesObj);
            });
            getAllCitiesJson(type, shippingAramexCitiesObj);


            function getAllCitiesJson(type, aramexCitiesObj) {
                var country_code = $(type).find(".aramex_countries").val();
                var url_check = "{$link->getAdminLink('AdminSerchautocities')|escape:'html':'UTF-8'}";
                shipping_aramex_cities_temp = '';
               /* aramexCitiesObj.autocomplete("option", "source", url_check);*/

                return $(type).find(".aramex_city").autocomplete(url_check, {
                    minChars: 2,
                    autoFill: true,
                    max:20,
                    matchContains: false,
                    mustMatch:false,
                    scroll:true,
                    dataType: 'json',
			formatItem: function(data, i, max, value, term) {
				return value;
			},
                    extraParams: {
                        country_code : country_code
                    },
                    parse: function(data) {
				var mytab = new Array();
				for (var i = 0; i < data.length; i++)
					mytab[mytab.length] = { data: data[i].trim(), value: data[i].trim() };
				return mytab;
                }
                }).result(function(e, i){
                        $(type).find(".aramex_city").val(i);
		});
            }
        }
    });
</script>
{include file='./calculate_rate.tpl'}
{include file='./schedule_pickup.tpl'}
{include file='./printlabel.tpl'}
{include file='./track.tpl'}

