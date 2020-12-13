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

<div class="schedule-pickup-part">
    <div class="pickup-form">
        <form method="post"
              action="{$link->getAdminLink('AdminSchedule')|escape:'html':'UTF-8'}"
              id="pickup-form">

            <input name="order_id" value={$order_id|escape:'html':'UTF-8'} type="hidden">
            <FIELDSET>
                <legend style="font-weight:bold; padding:0 5px;">Schedule Pickup</legend>
                <div class="fields mar-5">
                    <h3>Pickup Details</h3>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>Location:</label>
                            <input type="text" readonly="readonly" name="pickup[location]" id="pickup_location"
                                   value="Reception"/>
                        </div>
                        <div class="field fl">
                            <label>Vehicle Type:</label>
                            <select name="pickup[vehicle]" id="pickup_vehicle">
                                <option value="Bike">Small (no specific vehicle required)</option>
                                <option value="Car">Medium (regular car or small van)</option>
                                <option value="Truck">Large (large van or truck required)</option>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>Date: <span class="red">*</span></label>
                            <input type="text" readonly="readonly" name="pickup[date]" id="pickup_date"
                                   value="{$currentTimeM|escape:'html':'UTF-8'}"
                                   class="width-150 fl"/>
                        </div>
                        <div class="field fl f1l">
                            <label>Ready Time: <span class="red">*</span></label>
                            <select name="pickup[ready_hour]" class="width-60 fl" id="ready_hour">
                                {assign var = 'time' value = $currentTimeH }
                                {for $i=7 to 20}
                                    {if $i < 10}
                                        {assign var = 'val' value = "0{$i}"}
                                    {else}
                                        {assign var = 'val' value = $i}
                                    {/if}
                                    <option
                                            value="{$val|escape:'html':'UTF-8'}"
                                            {if $time eq $i}
                                                {l s='selected="selected"' mod='aramexshipping'}
                                            {else}
                                                {l s='' mod='aramexshipping'}
                                            {/if}
                                    >{$val|escape:'html':'UTF-8'}
                                    </option>
                                {/for}
                            </select>
                            <select name="pickup[ready_minute]" class="width-60  fl mar-lf-10" id="ready_minute">
                                {assign var = 'time' value = $currentTimei }
                                {for $i=0 to 55 step 5}
                                    {if $i < 10}
                                        {assign var = 'val' value = "0{$i}"}
                                    {else}
                                        {assign var = 'val' value = $i}
                                    {/if}
                                    <option
                                            value="{$val|escape:'html':'UTF-8'}"
                                            {if $time eq $i}
                                                {l s='selected="selected"' mod='aramexshipping'}
                                            {else}
                                                {l s='' mod='aramexshipping'}
                                            {/if}
                                    >{$val|escape:'html':'UTF-8'}
                                    </option>
                                {/for}
                            </select>
                            <div class="clearfix"></div>
                        </div>
                        <div class="field fl mar-lf-10 f1l">
                            <label>Closing Time: <span class="red">*</span></label>
                            <select name="pickup[latest_hour]" class="width-60 fl" id="latest_hour">
                                {assign var = 'time' value = $timePlusOne}
                                {for $i=7 to 20}
                                    {if $i < 10}
                                        {assign var = 'val' value = "0{$i}"}
                                    {else}
                                        {assign var = 'val' value = $i}
                                    {/if}
                                    <option
                                            value="{$val|escape:'html':'UTF-8'}"
                                            {if $time eq $i}
                                                {l s='selected="selected"' mod='aramexshipping'}
                                            {else}
                                                {l s='' mod='aramexshipping'}
                                            {/if}
                                    >{$val|escape:'html':'UTF-8'}
                                    </option>
                                {/for}
                            </select>
                            <select name="pickup[latest_minute]" class="width-60 fl mar-lf-10" id="latest_minute">
                                {assign var = 'time' value = $currentTimei }
                                {for $i=0 to 55 step 5}
                                    {if $i < 10}
                                        {assign var = 'val' value = "0{$i}"}
                                    {else}
                                        {assign var = 'val' value = $i}
                                    {/if}
                                    <option
                                            value="{$val|escape:'html':'UTF-8'}"
                                            {if $time eq $i}
                                                {l s='selected="selected"' mod='aramexshipping'}
                                            {else}
                                                {l s='' mod='aramexshipping'}
                                            {/if}
                                    >{$val|escape:'html':'UTF-8'}
                                    </option>
                                {/for}
                            </select>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>Reference 1:</label>
                            <input type="text" name="pickup[reference]" id="pickup_reference"
                                   value="{$order_id|escape:'html':'UTF-8'}"/>
                        </div>
                        <div class="field fl">
                            <label>Status: <span class="red">*</span></label>
                            <select name="pickup[status]" id="pickup_status">
                                <option value="Ready">Ready</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>Product Group: <span class="red">*</span></label>
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
                            <select name="pickup[product_group]" id="product_group">
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
                        </div>
                        <div class="field fl">
                            <label>Product Type: <span class="red">*</span></label>
                            <select name="pickup[product_type]" class="fl" id="product_type">
                                {assign var = 'dom' value = $allowed_method}
                                {assign var = 'exp' value = $allowed_method}
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
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>Payment Type: <span class="red">*</span></label>
                            <select name="pickup[payment_type]">
                                <option value="P">Prepaid</option>
                                <option value="C">Collect</option>
                            </select>
                        </div>
                        <div class="field fl">
                            <label>Weight <span class="red">*</span></label>
                            <div>
                                <input type="text" name="text_weight" id="text_weight"
                                       class="fl mar-right-10 width-60"
                                       value="{$totalWeight|escape:'html':'UTF-8'}"/>
                                <select name="pickup[weight_unit]" class="fl width-60">
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
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label class="width-150">Number of Pieces: <span class="red">*</span></label>
                            <input class="requried-entry" type="text" name="no_pieces" id="no_pieces"
                                   value="1"/>
                        </div>
                        <div class="field fl">
                            <label class="width-150">Number of Shipments: <span class="red">*</span></label>
                            <input type="text" name="no_shipments" class="requried-entry"
                                   id="no_shipments" value="1"/>
                        </div>
                    </div>

                </div>
                <div class="fields mar-10">
                    <h3>Address Information</h3>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>Company: <span class="red">*</span></label>
                            <input type="text" name="pickup[company]" id="pickup_company"
                                   value="{$company|escape:'html':'UTF-8'}"/>
                        </div>
                        <div class="field fl">
                            <label>Contact: <span class="red">*</span></label>
                            <input type="text" name="pickup[contact]" class="requried-entry" id="pickup_contact"
                                   value="{$name|escape:'html':'UTF-8'}"/>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>Phone: <span class="red">*</span></label>
                            <input type="text" name="pickup[phone]" id="pickup_phone" class="requried-entry"
                                   value="{$phone|escape:'html':'UTF-8'}"/>
                        </div>
                        <div class="field fl">
                            <label>Extension:</label>
                            <input type="text" name="pickup[ext]" id="pickup_ext" value=""/>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field">
                            <label>Mobile: <span class="red">*</span></label>
                            <input type="text" name="mobile" id="mobile" value="{$phone|escape:'html':'UTF-8'}"
                                   class="width-full required-entry"/>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field">
                            <label>Address: <span class="red">*</span></label>
                            <input type="text" name="address" id="address"
                                   value="{$address|escape:'html':'UTF-8'}"
                                   class="width-full required-entry"/>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>Country: <span class="red">*</span></label>
                            <select name="pickup[country]" id="pickup_country" class="aramex_countries">
                                {assign var='_country' value = $country}
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
                        <div class="field fl">
                            <label>State/Prov:</label>
                            <input type="text" name="pickup[state]" id="pickup_state"
                                   value="{$state|escape:'html':'UTF-8'}"/>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field fl width-270">
                            <label>City: <span class="red no-display">*</span></label>
                            <input type="text" name="city" id="city" class="aramex_city" autocomplete="off"
                                   value="{$city|escape:'html':'UTF-8'}"/>
                            <div id="pickup_city_autocomplete" class="am_autocomplete"></div>
                        </div>
                        <div class="field fl">
                            <label>Post Code: <span class="red no-display">*</span></label>
                            <input type="text" name="pickup[zip]" id="pickup_zip" class=" required-entry"
                                   value="{$postalcode|escape:'html':'UTF-8'}"/>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field">
                            <label>Email: <span class="red">*</span></label>
                            <input type="text" name="email" id="email" value="{$email|escape:'html':'UTF-8'}"
                                   class="width-full required-entry"/>
                        </div>
                    </div>
                    <div class="clearfix mar-5">
                        <div class="field">
                            <label>Comments:</label>
                            <input type="text" name="pickup[comments]" id="pickup_comments" value=""
                                   class="width-full"/>
                        </div>
                    </div>
                    <div class="cal-button-part">
                        <button name="aramex_pickup_submit" type="button" class='button-primary'
                                id="aramex_pickup_submit">Submit
                        </button>
                        <button type="button" class='button-primary' onclick="myObj.close()">Close</button>
                        <span class="mar-lf-10 red">* are required fields</span>
                        <input type="hidden" value="{$order_id|escape:'html':'UTF-8'}"
                               name="pickup[order_id]"/>
                    </div>
                    <div class="aramex_loader"
                         style="height:60px; margin:10px 0; background-position-x: center; display:none; background-repeat: no-repeat; background-image: url('{$preloader|escape:'html':'UTF-8'}">
                    </div>
                    <div class="pickup-result mar-10">
                        <h3>Result</h3>
                        <div class="pickup-res mar-10"></div>
                    </div>
                </div>
            </FIELDSET>
        </form>
        <script type="text/javascript">

            $(document).ready(function () {
                $("#product_type").chained("#product_group");
                var H = {$currentTimeH|escape:'html':'UTF-8'}
                var M = {$currentTimei|escape:'html':'UTF-8'}
                var mounth = {$M|escape:'html':'UTF-8'}
                var day = {$D|escape:'html':'UTF-8'}
                var year = {$Y|escape:'html':'UTF-8'}

                    $('#pickup_date').datepicker({
                        dateFormat: 'mm/dd/yy'
                    });
                $('#aramex_pickup_submit').click(function () {

                    if ($('#pickup-form').validate({
                                rules: {
                                    mobile: {
                                        required: true,
                                    },
                                    address: {
                                        required: true,
                                    },
                                    city: {
                                        required: true,
                                    },
                                    email: {
                                        required: true,
                                    },
                                    text_weight: {
                                        required: true,
                                    },
                                    no_pieces: {
                                        required: true,
                                    },
                                    no_shipments: {
                                        required: true,
                                    }
                                }
                            }
                        )) {
                        if ($("#pickup-form").valid()) {
                            var rH = $('#ready_hour').val();
                            var lH = $('#latest_hour').val();
                            var rM = $('#ready_minute').val();
                            var lM = $('#latest_minute').val();
                            var error = false;
                            var rDate = $('#pickup_date').val();
                            if (rDate == '' || rDate == null) {
                                alert("Pickup Date should not empty");
                                return;
                            }
                            rDate = rDate.split("/");


                            var isCheckTime = false;
                            if (rDate[2] < year) {
                                error = true;
                            } else if (rDate[2] == year) {

                                if (rDate[0] < mounth) {
                                    error = true;
                                } else if (rDate[0] == mounth) {
                                    if (rDate[1] < day) {
                                        error = true;
                                    } else if (rDate[1] == day) {
                                        if (rH < H) {
                                            alert("Ready Time should be greater than Current Time");
                                            return;
                                        } else if (rH == H && rM < M) {
                                            alert("Ready Time should be greater than Current Time");
                                            return;
                                        }
                                        isCheckTime = true;
                                    }
                                }
                            }
                            if (error) {
                                alert("Pickup Date should be greater than Current Date");
                                return;
                            }
                            if (isCheckTime) {
                                if (lH < rH) {
                                    error = true;
                                } else if (lH <= rH && lM <= rM) {
                                    error = true;
                                }
                                if (error) {
                                    alert("Closing Time always greater than Ready Time");
                                    return;
                                }
                            }
                            if ($("#pickup-form").valid()) {
                                myObj.schedulePickup();
                            }
                            return false;
                        }
                    }
                });

            });

        </script>
    </div>
</div>
