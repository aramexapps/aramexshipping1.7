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

/**
 * Controller for Printing label functionality
 */
class AdminRateController extends AdminController
{
    /**
     * @var string $helper Object of helper class
     */
    protected $helper;

    /**
     * Checks if request comes not from another site
     *
     * @return mixed
     */
    public function init()
    {
        include_once _PS_MODULE_DIR_ . 'aramexshipping/classes/core/AramexHelper.php';
        $this->helper = new AramexHelper();
        #security reason
        parent::init();
        $referer_parced_url = parse_url($_SERVER['HTTP_REFERER']);
        $base_url_parced = parse_url(_PS_BASE_URL_);
        if (!(isset($_SERVER['HTTP_REFERER']) || $referer_parced_url['host'] == $base_url_parced['host'])) {
            Tools::Redirect(__PS_BASE_URI__);
            die;
        } else {
            $this->ajax = true;
        }
    }

    /**
     * Starting method
     *
     * @return mixed|string|void
     */
    public function initContent()
    {

        if ($this->ajax == true) {
            $info = $this->helper->getInfo();
            $account = $info['clientInfo']['AccountNumber'];
            $response = array();
            try {
                $country_code = $info['clientInfo']['country'];
                $countries = Country::getCountries(Context::getContext()->language->id);
                foreach ($countries as $value) {
                    if ($value['iso_code'] == $country_code) {
                        $countryName = $value['name'];
                    }
                }

                $countryName = ($countryName) ? $countryName : "";
                $params = array(
                    'ClientInfo' => $info['clientInfo'],
                    'Transaction' => array(
                        'Reference1' => Tools::getValue('reference')
                    ),
                    'OriginAddress' => array(
                        'StateOrProvinceCode' => html_entity_decode(Tools::getValue('origin_state')),
                        'City' => html_entity_decode(Tools::getValue('origin_city')),
                        'PostCode' => Tools::getValue('origin_zipcode'),
                        'CountryCode' => Tools::getValue('origin_country')
                    ),
                    'DestinationAddress' => array(
                        'StateOrProvinceCode' => html_entity_decode(Tools::getValue('destination_state')),
                        'City' => html_entity_decode(Tools::getValue('destination_city')),
                        'PostCode' => Tools::getValue('destination_zipcode'),
                        'CountryCode' => Tools::getValue('destination_country'),
                    ),
                    'ShipmentDetails' => array(
                        'PaymentType' => Tools::getValue('payment_type'),
                        'ProductGroup' => Tools::getValue('product_group'),
                        'ProductType' => Tools::getValue('service_type'),
                        'ActualWeight' => array(
                            'Value' => Tools::getValue('text_weight'),
                            'Unit' =>
                                Tools::getValue('weight_unit')
                        ),
                        'ChargeableWeight' => array(
                            'Value' => Tools::getValue('text_weight'),
                            'Unit' =>
                                Tools::getValue('weight_unit')
                        ),
                        'NumberOfPieces' => Tools::getValue('total_count')
                    ),
                    'PreferredCurrencyCode' => Tools::getValue('currency_code')
                );

                //SOAP object
                $soapClient = new SoapClient(
                    $info['baseUrl'] . 'aramex-rates-calculator-wsdl.wsdl',
                    array('soap_version' => SOAP_1_1, 'cache_wsdl' => WSDL_CACHE_NONE)
                );
                try {
                    $results = $soapClient->CalculateRate($params);
                    if ($results->HasErrors) {
                        if (count($results->Notifications->Notification) > 1) {
                            $error = "";
                            foreach ($results->Notifications->Notification as $notify_error) {
                                $error .= 'Aramex: ' . $notify_error->Code . ' - ' . $notify_error->Message . "<br>";
                            }
                            $response['error'] = $error;
                        } else {
                            $response['error'] = 'Aramex: ' . $results->Notifications->Notification->Code . ' - ' .
                                $results->Notifications->Notification->Message;
                        }
                        $response['type'] = 'error';
                    } else {
                        $response['type'] = 'success';
                        $amount = "<p class='amount'>" . $results->TotalAmount->Value . " " . $results->TotalAmount->
                            CurrencyCode . "</p>";
                        $text = "Local taxes - if any - are not included. Rate is based on account number 
                        $account in " . $countryName;
                        $response['html'] = $amount . $text;
                    }
                } catch (Exception $e) {
                    $response['type'] = 'error';
                    $response['error'] = $e->getMessage();
                }
            } catch (Exception $e) {
                $response['type'] = 'error';
                $response['error'] = $e->getMessage();
            }
            print json_encode($response);
            die();
        }
    }
}
