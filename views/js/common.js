/**
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
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

$(document).ready(function () {
    // $( "#aramex_overlay" ).insertBefore( $(  "#post" ));
    $("#create_aramex_shipment").insertBefore($("#order_data"));
    $("#create_aramex_shipment").css("display", "inline-block");
    $("#track_aramex_shipment").insertBefore($("#order_data"));
    $("#track_aramex_shipment").css("display", "inline-block");
    $("#print_aramex_shipment").insertBefore($("#order_data"));
    $("#print_aramex_shipment").css("display", "inline-block");

    function aramexpop() {
        $("#aramex_overlay").css("display", "block");
        $("#aramex_shipment").css("display", "block");
        $("#aramex_shipment_creation").fadeIn(1000);
    }

    $("#create_aramex_shipment").click(function () {
        aramexpop();
    });
    $("#aramex_close").click(function () {
        aramex_close();
    });
});

function aramex_close() {
    $("#aramex_shipment").css("display", "none");
    $("#aramex_overlay").css("display", "none");
}
