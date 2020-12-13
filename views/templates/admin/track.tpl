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

<div id="track_overlay" style="display:none;">
    <div class="track-form" style="display:none;">
        <form method="post" action="" id="track-form">
            <FIELDSET>
                <legend style="font-weight:bold; padding:0 5px;">Track Aramex Shipment</legend>
                {if $last_track != "" }
                    <input name="aramex-track" id="aramex-track-field"
                           value="{$last_track|escape:'html':'UTF-8'}"/>
                {else}
                    <p>Aramex shipment was not created</p>
                {/if}
            </FIELDSET>
            <div class="aramex_loader"
                 style="background-image: url({$preloader|escape:'html':'UTF-8'});  height:60px; margin:10px 0; background-position-x: center; display:none; background-repeat: no-repeat; ">
            </div>
            <div class="track-result mar-10" style="display:none;">
                <h3>Result</h3>
                <div class="result mar-10"></div>
            </div>
            <button id="aramex_track_submit_id" type="button" name="aramex_track_submit" class="button-primary">
                Track Shipment
            </button>
            <button id="track_close" class="button-primary" type="button">Close</button>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#track_aramex_shipment').click(function () {
                        $('.track-result').css("display", "none");
                        $('#track_overlay').css("display", "block");
                        $('.track-form').css("display", "block");
                    });
                    $('#aramex_track_submit_id').click(function () {
                        myObj.track();
                    });
                    $('#track_close').click(function () {
                        $('#track_overlay').css("display", "none");
                    });
                });

            </script>
        </form>
    </div>
</div>
