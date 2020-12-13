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
{if isset($smarty.session.aramex_errors_printlabel)  and $smarty.session.aramex_errors_printlabel gt '0'}
    <div class="aramex_errors">
        {foreach from=$smarty.session.aramex_errors_printlabel key=key item=error}
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
        {/foreach}
    </div>
    <button id="printlabel_close" class="button-primary" type="button">Close</button>
{/if}

<div id="printlabel_overlay" style="display:none;">
    <form method="post" action="{$link->getAdminLink('AdminPrintlabel')|escape:'html':'UTF-8'}" id="printlabel-form">
        <input type="hidden" name="aramex_shipment_referer"
               value="{$smarty.server.HTTP_HOST|escape:'html':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}"/>
        <input name="aramex-printlabel" id="aramex-printlabel-field" type="hidden"
               value="{$order_id|escape:'html':'UTF-8'}"/>
        <input name="aramex-lasttrack" id="aramex-printlabel-field" type="hidden"
               value="{$last_track|escape:'html':'UTF-8'}"/>
        <script type="text/javascript">
            $(document).ready(function () {
                $('#print_aramex_shipment').click(function () {
                    $('#printlabel-form').submit();
                });
                $('#printlabel_close').click(function () {
                    $('#printlabel_overlay').css("display", "none");
                });
            });
        </script>
    </form>
</div>
