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

include_once _PS_MODULE_DIR_ . 'aramexshipping/classes/core/AramexHelper.php';

class AramexAramexcalculatorMethodModel extends AramexHelper
{
    private $form_fields;

    public function __construct()
    {
        $this->form_fields = new DataAramexSettings();
    }
    public function calculateRate($address)
    {
        $destinationCountry = $address['country_code'];
        $destinationCity = $address['city'];
        $destinationZipcode = $address['post_code'];
        $product = new Product($address['product_id']);
        $weight = $product->weight;
        $currency = $address['currency'];
        $allowed_methods = array();

        $form_fields = $this->form_fields->getAllElements();

        $allowed_methods = array();
        if (Configuration::get('ARAMEX_COUNTRY') == $address['country_code']) {
            $product_group = 'DOM';
            $allowed_methods_all = $form_fields['allowed_domestic_methods'];

            foreach ($allowed_methods_all as $allowed_method_internal) {
                foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_DOMESTIC_METHODS')) as $item) {
                    if ($allowed_method_internal['id_option'] == $item) {
                        $allowed_methods[$allowed_method_internal['id_option']] = $allowed_method_internal['name'];
                    }
                }
            }
        } else {
            $allowed_methods_all = $form_fields['allowed_international_methods'];
            $product_group = 'EXP';
            foreach ($allowed_methods_all as $allowed_method_internal) {
                foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_INTERNATIONAL_METHODS')) as $item) {
                    if ($allowed_method_internal['id_option'] == $item) {
                        $allowed_methods[$allowed_method_internal['id_option']] = $allowed_method_internal['name'];
                    }
                }
            }
        }

        $info = $this->getInfo();
        //$response = array();
        $OriginAddress = array(
            'StateOrProvinceCode' => Configuration::get('ARAMEX_STATE'),
            'City' => Configuration::get('ARAMEX_CITY'),
            'PostCode' => Configuration::get('ARAMEX_POSTALCODE'),
            'CountryCode' => Configuration::get('ARAMEX_COUNTRY'),
        );
        $DestinationAddress = array(
            'StateOrProvinceCode' => "",
            'City' => $destinationCity,
            'PostCode' => $destinationZipcode,
            'CountryCode' => $destinationCountry,
        );
        $ShipmentDetails = array(
            'PaymentType' => 'P',
            'ProductGroup' => $product_group,
            'ProductType' => '',
            'ActualWeight' => array('Value' => $weight, 'Unit' => Configuration::get('PS_WEIGHT_UNIT')),
            'ChargeableWeight' => array('Value' => $weight, 'Unit' => Configuration::get('PS_WEIGHT_UNIT')),
            'NumberOfPieces' => 1
        );

        $params = array(
            'ClientInfo' => $info['clientInfo'],
            'OriginAddress' => $OriginAddress,
            'DestinationAddress' => $DestinationAddress,
            'ShipmentDetails' => $ShipmentDetails,
            'PreferredCurrencyCode' => $currency
        );

        //SOAP object
        $soapClient = new SoapClient($info['baseUrl'] . 'aramex-rates-calculator-wsdl.wsdl');

        $priceArr = array();
        foreach ($allowed_methods as $m_value => $m_title) {
            $params['ShipmentDetails']['ProductType'] = $m_value;
            if ($m_value == "CDA") {
                $params['ShipmentDetails']['Services'] = "CODS";
            } else {
                $params['ShipmentDetails']['Services'] = "";
            }
            try {
                $results = $soapClient->CalculateRate($params);
                if ($results->HasErrors) {
                    if (count($results->Notifications->Notification) > 1) {
                        foreach ($results->Notifications->Notification as $notify_error) {
                            $priceArr[$m_value] = ('Aramex: ' . $notify_error->Code . ' - ' . $notify_error->Message) .
                                ' ';
                        }
                    } else {
                        $priceArr[$m_value] = ('Aramex: ' . $results->Notifications->Notification->Code . ' - ' .
                            $results->Notifications->Notification->Message) . ' ';
                    }
                    $priceArr['type'] = 'error';
                } else {
                    $priceArr['type'] = 'success';
                    $priceArr[$m_value] = array('label' => $m_title, 'amount' => $results->TotalAmount->Value,
                        'currency' => $results->TotalAmount->CurrencyCode);
                }
            } catch (Exception $e) {
                $priceArr['type'] = 'error';
                $priceArr[$m_value] = $e->getMessage();
            }
        }

        print json_encode($priceArr);
        die();
    }
}
