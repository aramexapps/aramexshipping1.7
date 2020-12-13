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
 * Controller for Schedule functionality
 */
class AdminScheduleController extends AdminController
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
            $response = array();
            try {
                $post_array = Tools::getValue('pickup');
                $pickupDate = strtotime($post_array['date']);
                $readyTimeH = $post_array['ready_hour'];
                $readyTimeM = $post_array['ready_minute'];
                $readyTime = mktime(
                    ($readyTimeH - 2),
                    $readyTimeM,
                    0,
                    date(
                        "m",
                        $pickupDate
                    ),
                    date(
                        "d",
                        $pickupDate
                    ),
                    date(
                        "Y",
                        $pickupDate
                    )
                );
                $closingTimeH = $post_array['latest_hour'];
                $closingTimeM = $post_array['latest_minute'];
                $closingTime = mktime(
                    ($closingTimeH - 2),
                    $closingTimeM,
                    0,
                    date(
                        "m",
                        $pickupDate
                    ),
                    date(
                        "d",
                        $pickupDate
                    ),
                    date(
                        "Y",
                        $pickupDate
                    )
                );
                $params = array(
                    'ClientInfo' => $info['clientInfo'],
                    'Transaction' => array(
                        'Reference1' => $post_array['reference']
                    ),
                    'Pickup' => array(
                        'PickupContact' => array(
                            'PersonName' => html_entity_decode($post_array['contact']),
                            'CompanyName' => html_entity_decode($post_array['company']),
                            'PhoneNumber1' => html_entity_decode($post_array['phone']),
                            'PhoneNumber1Ext' => html_entity_decode($post_array['ext']),
                            'CellPhone' => html_entity_decode(Tools::getValue('mobile')),
                            'EmailAddress' => html_entity_decode(Tools::getValue('email'))
                        ),
                        'PickupAddress' => array(
                            'Line1' => html_entity_decode(Tools::getValue('address')),
                            'City' => html_entity_decode(Tools::getValue('city')),
                            'StateOrProvinceCode' => html_entity_decode($post_array['state']),
                            'PostCode' => html_entity_decode($post_array['zip']),
                            'CountryCode' => $post_array['country']
                        ),
                        'PickupLocation' => html_entity_decode($post_array['location']),
                        'PickupDate' => $readyTime,
                        'ReadyTime' => $readyTime,
                        'LastPickupTime' => $closingTime,
                        'ClosingTime' => $closingTime,
                        'Comments' => html_entity_decode($post_array['comments']),
                        'Reference1' => html_entity_decode($post_array['reference']),
                        'Reference2' => '',
                        'Vehicle' => $post_array['vehicle'],
                        'Shipments' => array(
                            'Shipment' => array()
                        ),
                        'PickupItems' => array(
                            'PickupItemDetail' => array(
                                'ProductGroup' => $post_array['product_group'],
                                'ProductType' => $post_array['product_type'],
                                'Payment' => $post_array['payment_type'],
                                'NumberOfShipments' => Tools::getValue('no_shipments'),
                                'NumberOfPieces' => Tools::getValue('no_pieces'),
                                'ShipmentWeight' => array(
                                    'Value' => Tools::getValue('text_weight'),
                                    'Unit' => $post_array['weight_unit']
                                ),
                            ),
                        ),
                        'Status' => $post_array['status']
                    )
                );

                //SOAP object
                $soapClient = new SoapClient(
                    $info['baseUrl'] . 'shipping.wsdl',
                    array('soap_version' => SOAP_1_1)
                );
                try {
                    $results = $soapClient->CreatePickup($params);
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
                        $comment_content = "Pickup reference number - " . $results->ProcessedPickup->ID;
                        $this->helper->saveNote((int)$post_array['order_id'], $comment_content);

                        $response['type'] = 'success';
                        $amount = "<p class='amount'>Pickup reference number ( <strong>" . $results->ProcessedPickup->
                            ID . "</strong> ).</p>";
                        $response['html'] = $amount;
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
