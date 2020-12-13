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

<div class="back-over"></div>
<div class="cal-rate-part">
    <div class="cal-form">
        <form method="post" action="" id="calc-rate-form">
            <FIELDSET>
                <legend style="font-weight:bold; padding:0 5px;">Calculate Rates</legend>
                <div class="fields mar-10  aramex_top">
                    <h3>Shipment Origin</h3>
                    <div class="clearfix mar-10">
                        <div class="field fl width-270">
                            <label>Country <span class="red">*</span></label>
                            <select name="origin_country" class="arm_country aramex_countries" id="origin_country">
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
                        <div class="field fl">
                            <label>City <span class="red no-display">*</span></label>
                            <input type="text" name="origin_city" id="origin_city" class="aramex_city" value="{$city|escape:'html':'UTF-8'}"/>
                            <div id="origin_city_autocomplete" class="am_autocomplete"></div>
                        </div>
                    </div>
                    <div class="clearfix mar-10">
                        <div class="field fl width-270">
                            <label>Zip code <span class="red no-display">*</span></label>
                            <input type="text" name="origin_zipcode" id="origin_zipcode" value="{$postalcode|escape:'html':'UTF-8'}"/>
                        </div>
                        <div class="field fl">
                            <label>State / Province</label>
                            <input type="text" name="origin_state" id="origin_state" value="{$state|escape:'html':'UTF-8'}"/>
                        </div>
                    </div>
                </div>
                <div class="fields mar-10  aramex_bottom">
                    <h3>Shipment Destination</h3>
                    <div class="clearfix mar-10">
                        <div class="field fl width-270">
                            <label>Country <span class="red">*</span></label>
                            <select name="destination_country" class="arm_country aramex_countries"
                                    id="destination_country">
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
                            <label>City <span class="red no-display">*</span></label>
                            {if isset($reciverCity)}
                                {assign var = '_reciverCity'  value = $reciverCity }
                            {else}
                                {assign var = '_reciverCity' value = ""}
                            {/if}
                            <input type="text" name="destination_city" autocomplete="off" id="destination_city"
                                   class="aramex_city" value="{$_reciverCity|escape:'html':'UTF-8'}"/>
                            <div id="destination_city_autocomplete" class="am_autocomplete "></div>
                        </div>
                    </div>
                    <div class="clearfix mar-10">
                        <div class="field fl width-270">
                            <label>Zip code <span class="red no-display">*</span></label>
                            {if isset($reciverPostcode)}
                                {assign var = '_reciverPostcode'  value = $reciverPostcode }
                            {else}
                                {assign var = '_reciverPostcode' value = ""}
                            {/if}

                            <input type="text" name="destination_zipcode" id="destination_zipcode"
                                   value="{$_reciverPostcode|escape:'html':'UTF-8'}"/>
                        </div>
                        <div class="field fl">
                            {if isset($address_reciver)}
                                {assign var = '_address_reciver'  value = $address_reciver }
                            {else}
                                {assign var = '_address_reciver' value = ""}
                            {/if}
                            <label>State / Province</label>
                            <input type="text" name="destination_state" id="destination_state"
                                   value="{$_address_reciver|escape:'html':'UTF-8'}"/>
                        </div>
                    </div>
                </div>
                <div class="fields mar-10">
                    <div class="clearfix mar-10">
                        <div class="field fl width-270">
                            <label>Payment Type <span class="red">*</span></label>
                            <select name="payment_type">
                                <option value="P">Prepaid</option>
                                <option value="C">Collect</option>
                                <option value="3">Third Party</option>
                            </select>
                        </div>
                        <div class="field fl">
                            <label>Product Type <span class="red">*</span></label>
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

                            <select name="product_group" id="calc-product-group">
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
                    </div>
                    <div class="clearfix mar-10">
                        <div class="field fl width-270">
                            <label>Service Type <span class="no-display">*</span></label>
                            {assign var = 'dom' value = $allowed_method}
                            {assign var = 'exp' value = $allowed_method}
                            <select name="service_type" class="fl" id="service_type">
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
                        <div class="field fl">
                            <label>Weight <span class="red">*</span></label>
                            <div>
                                <input type="text" name="text_weight" class="fl mar-right-10 width-60"
                                       value="{$totalWeight|escape:'html':'UTF-8'}"/>
                                <select name="weight_unit" class="fl width-60">
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
                    <div class="clearfix mar-10">
                        <div class="field fl width-270">
                            <label style="width:270px;">Number of Pieces: <span class="red ">*</span></label>
                            <input type="text" value="1" name="total_count" class="fl"/>
                        </div>
                        <div class="field fl width-270">
                            <label>Preferred Currency Code: </label>
                            <input type="text" value="{$currencyCode|escape:'html':'UTF-8'}" name="currency_code" class="fl"/>
                        </div>
                    </div>
                    <div class="cal-button-part">
                        <button name="aramex_calc_rate_submit" type="button" id="aramex_calc_rate_submit"
                                class="button-primary">Caclculate
                        </button>
                        <button type="button" class="button-primary" onclick="myObj.close()">Close</button>
                        <span class="mar-lf-10 red">* are required fields</span>
                        <input type="hidden" value={$order_id|escape:'html':'UTF-8'}" name=" reference" />

                    </div>
                    <div class="aramex_loader"
                         style="background-image: url({$preloader|escape:'html':'UTF-8'}); height:60px; margin:10px 0; background-position-x: center; display:none; background-repeat: no-repeat; ">

                    </div>
                    <div class="rate-result mar-10">

                        <h3>Result</h3>
                        <div class="result mar-10"></div>
                    </div>
                </div>
            </FIELDSET>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#aramex_calc_rate_submit').click(function () { // The button type should be "button" and not submit
                        if ($('#calc-rate-form').validate(
                                {
                                    rules: {
                                        origin_zipcode: {
                                            required: true,
                                        },
                                        destination_city: {
                                            required: true,
                                        },
                                    },
                                }
                            )) {

                            if ($("#calc-rate-form").valid()) {
                                myObj.calcRate();
                            }
                            //   return false;
                        }
                    });
                    $("#service_type").chained("#calc-product-group");
                });
            </script>
        </form>
    </div>
</div>