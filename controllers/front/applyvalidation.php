<?php
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
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2019 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class AramexshippingApplyvalidationModuleFrontController extends ModuleFrontController
{
    /**
     * @var $model Object of AramexSerchautocitiesMethodModel class
     */
    private $model;

    /**
     * Checks if request comes not from another site
     *
     * @return mixed
     */
    public function init()
    {
        #security reason
        parent::init();
        $referer_parced_url = parse_url(_PS_BASE_URL_);
        $base_url_parced = parse_url(_PS_BASE_URL_);
        if (!(isset($_SERVER['HTTP_REFERER']) || $referer_parced_url['host'] == $base_url_parced['host'])) {
            Tools::Redirect(__PS_BASE_URI__);
            die;
        } else {
            parent::init();
            $this->ajax = true;
        }
        include_once _PS_MODULE_DIR_ . 'aramexshipping/classes/core/AramexSerchautocitiesMethodModel.php';
        $this->model = new AramexSerchautocitiesMethodModel();
    }

    /**
     * Starting method
     *
     * @return  array
     */
    public function initContent()
    {
        if ($this->ajax) {
            $address = array();
            $address['city'] = Tools::getValue('city') ? Tools::getValue('city') : "";
            $address['post_code'] = Tools::getValue('post_code') ? Tools::getValue('post_code') : "";
            $address['country_code'] = Tools::getValue('country_code') ? Country::getIsoById(
                Tools::getValue('country_code')
            ) : "";
            $result = $this->model->validateAddress($address);
            if (count($result) > 0 && $result != false) {
                echo json_encode($result);
                die();
            } else {
                echo json_encode(array());
                die();
            }
        }
    }
}
