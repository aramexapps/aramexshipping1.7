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
 * Controller for for Bulk functionality
 */
class AdminBulkController extends AdminController
{

    /**
     * @var string $helper Object of helper class
     */
    protected $helper;

    /**
     * @var array $aramex_errors Array of errors
     */
    protected $aramex_errors = array();

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
            $post = array();
            $params = array();
            $params1 = array();
            parse_str(Tools::getValue('str'), $params1);
            $orders = array();
            $post['aramex_shipment_shipper_country'] = Configuration::get('ARAMEX_COUNTRY');
            //check "pending" status
            if (count(Tools::getValue("selectedOrders"))) {
                foreach (Tools::getValue("selectedOrders") as $key => $order_id) {
                    $order = new Order((int)$order_id);
                    if ($order->getCurrentState() == 10 || $order->getCurrentState() == 2 ||
                        $order->getCurrentState() == 1) {
                        $address = new Address($order->id_address_delivery);
                        $shippingCountry = Country::getIsoById($address->id_country);
                        if ($shippingCountry == $post['aramex_shipment_shipper_country']) {
                            $orders[$key]['method'] = "DOM";
                        } else {
                            $orders[$key]['method'] = "EXP";
                        }
                        $orders[$key]['order_id'] = $order_id;
                    } else {
                        $responce = "<p class='aramex_red'> Select orders with 'Awaiting bank wire payment 
                        or Payment accepted or Awaiting check payment' status, please</p>";
                        echo json_encode(array('message' => $responce));
                        die();
                    }
                }
                //domestic metods must be first
                $dom = array();
                $exp = array();
                foreach ($orders as $key => $order_item) {
                    if ($order_item['method'] == 'DOM') {
                        $dom[$key]['method'] = "DOM";
                        $dom[$key]['order_id'] = $order_item['order_id'];
                    } else {
                        $exp[$key]['method'] = "EXP";
                        $exp[$key]['order_id'] = $order_item['order_id'];
                    }
                }
                $orders = array();
                $total = count($dom) + count($exp);
                for ($i = 0; $i < $total; $i++) {
                    foreach ($dom as $key => $item) {
                        $orders[$key]['method'] = "DOM";
                        $orders[$key]['order_id'] = $item['order_id'];
                    }
                    foreach ($exp as $key => $item) {
                        $orders[$key]['method'] = "EXP";
                        $orders[$key]['order_id'] = $item['order_id'];
                    }
                }
            }

            //domestic metods must be first
            if (count($orders)) {
                $responce = "";
                foreach ($orders as $key => $orderItem) {
                    $post['aramex_shipment_original_reference'] = (int)$orderItem['order_id'];
                    $order = new Order((int)$orderItem['order_id']);
                    $itemsv = $order->getProductsDetail();
                    $totalWeight = 0;
                    $descriptionOfGoods = "";
                    foreach ($itemsv as $itemvv) {
                        if ($itemvv['product_quantity'] > 0) {
                            $weight = $itemvv['product_weight'] *
                                $itemvv['product_quantity'];
                            $descriptionOfGoods .= $itemvv['product_id'] . ' - ' .
                                trim($itemvv['product_name']) . ' : ';
                            $totalWeight += $weight;
                            $qty = $itemvv['product_quantity'];
                        }
                    }
                    $descriptionOfGoods = Tools::substr(
                        $descriptionOfGoods,
                        0,
                        100
                    );
                    if ($orderItem['method'] == 'DOM') {
                        $aramex_shipment_info_product_type = ($params1['aramex_shipment_info_product_type_dom']) ?
                            $params1['aramex_shipment_info_product_type_dom'] : "";
                        $aramex_shipment_info_payment_type = ($params1['aramex_shipment_info_payment_type_dom']) ?
                            $params1['aramex_shipment_info_payment_type_dom'] : "";
                        $aramex_shipment_info_payment_option = "";
                        $aramex_shipment_info_service_type = ($params1['aramex_shipment_info_service_type_dom']) ?
                            $params1['aramex_shipment_info_service_type_dom'] : "";
                        $aramex_shipment_currency_code = ($params1['aramex_shipment_currency_code_dom']) ?
                            $params1['aramex_shipment_currency_code_dom'] : "";
                        $aramex_shipment_info_custom_amount = "";
                    } else {
                        $aramex_shipment_info_product_type = ($params1['aramex_shipment_info_product_type']) ?
                            $params1['aramex_shipment_info_product_type'] : "";
                        $aramex_shipment_info_payment_type = ($params1['aramex_shipment_info_payment_type']) ?
                            $params1['aramex_shipment_info_payment_type'] : "";
                        $aramex_shipment_info_payment_option = ($params1['aramex_shipment_info_payment_option']) ?
                            $params1['aramex_shipment_info_payment_option'] : "";
                        $aramex_shipment_info_service_type = ($params1['aramex_shipment_info_service_type']) ?
                            $params1['aramex_shipment_info_service_type'] : "";
                        $aramex_shipment_currency_code = ($params1['aramex_shipment_currency_code']) ?
                            $params1['aramex_shipment_currency_code'] : "";
                        $aramex_shipment_info_custom_amount = ($params1['aramex_shipment_info_custom_amount']) ?
                            $params1['aramex_shipment_info_custom_amount'] : "";
                    }
                    $address = new Address($order->id_address_delivery);
                    $billing = new Address($order->id_address_invoice);

                    $company_name = isset($billing->country) ? $billing->company : '';
                    if ($company_name == "") {
                        $company_name = $address->company;
                    }
                    if ($company_name == "") {
                        $company_name = $address->firstname . " " . $address->lastname;
                    }
                    $id_customer = $order->id_customer;
                    $customer = new Customer((int)$id_customer);
                    //shipper parameters
                    $params['Shipper'] = array(
                        'Reference1' => (string)$orderItem['order_id'],
                        'Reference2' => '',
                        'AccountNumber' => (string)Configuration::get('ARAMEX_ACCOUNT_NUMBER'),
                        //Party Address
                        'PartyAddress' => array(
                            'Line1' => addslashes(Configuration::get('ARAMEX_ADDRESS')),
                            'Line2' => '',
                            'Line3' => '',
                            'City' => Configuration::get('ARAMEX_CITY'),
                            'StateOrProvinceCode' => Configuration::get('ARAMEX_STATE'),
                            'PostCode' => Configuration::get('ARAMEX_POSTALCODE'),
                            'CountryCode' => Configuration::get('ARAMEX_COUNTRY'),
                        ),
                        //Contact Info
                        'Contact' => array(
                            'Department' => '',
                            'PersonName' => Configuration::get('ARAMEX_NAME'),
                            'Title' => '',
                            'CompanyName' => Configuration::get('ARAMEX_COMPANY'),
                            'PhoneNumber1' => Configuration::get('ARAMEX_PHONE'),
                            'PhoneNumber1Ext' => '',
                            'PhoneNumber2' => '',
                            'PhoneNumber2Ext' => '',
                            'FaxNumber' => '',
                            'CellPhone' => Configuration::get('ARAMEX_PHONE'),
                            'EmailAddress' => Configuration::get('ARAMEX_EMAIL_ORIGIN'),
                            'Type' => ''
                        ),
                    );

                    //consinee parameters
                    $params['Consignee'] = array(
                        'Reference1' => (string)$orderItem['order_id'],
                        'Reference2' => '',
                        'AccountNumber' => "",
                        //Party Address
                        'PartyAddress' => array(
                            'Line1' => ($address->address1) ? $address->address1 . " " . $address->address2 : '',
                            'Line2' => '',
                            'Line3' => '',
                            'City' => ($address->city) ? $address->city : '',
                            'StateOrProvinceCode' => '',
                            'PostCode' => ($address->postcode) ? $address->postcode : '',
                            'CountryCode' => ($address->country) ? Country::getIsoById($address->id_country) : '',
                        ),
                        //Contact Info
                        'Contact' => array(
                            'Department' => '',
                            'PersonName' => ($address->firstname) ? $address->firstname . " " . $address->lastname : '',
                            'Title' => '',
                            'CompanyName' => $company_name,
                            'PhoneNumber1' => ($billing->phone) ? $billing->phone : '',
                            'PhoneNumber1Ext' => '',
                            'PhoneNumber2' => '',
                            'PhoneNumber2Ext' => '',
                            'FaxNumber' => '',
                            'CellPhone' => ($billing->phone) ? $billing->phone : '',
                            'EmailAddress' => ($customer->email) ? $customer->email : '',
                            'Type' => ''
                        )
                    );

                    // Other Main Shipment Parameters
                    $params['Reference1'] = (string)$orderItem['order_id'];
                    $params['Reference2'] = '';
                    $params['Reference3'] = '';
                    $params['ForeignHAWB'] = '';

                    $params['TransportType'] = 0;
                    $params['ShippingDateTime'] = time(); //date('m/d/Y g:i:sA');
                    $params['DueDate'] = time() + (7 * 24 * 60 * 60); //date('m/d/Y g:i:sA');
                    $params['PickupLocation'] = 'Reception';
                    $params['PickupGUID'] = '';
                    $params['Comments'] = '';
                    $params['AccountingInstrcutions'] = '';
                    $params['OperationsInstructions'] = '';
                    $params['Details'] = array(
                        'Dimensions' => array(
                            'Length' => '0',
                            'Width' => '0',
                            'Height' => '0',
                            'Unit' => 'cm'
                        ),
                        'ActualWeight' => array(
                            'Value' => (string)$totalWeight,
                            'Unit' => Configuration::get('PS_WEIGHT_UNIT')
                        ),
                        'ProductGroup' => $orderItem['method'],
                        'ProductType' => $aramex_shipment_info_product_type,
                        'PaymentType' => $aramex_shipment_info_payment_type,
                        'PaymentOptions' => $aramex_shipment_info_payment_option,
                        'Services' => $aramex_shipment_info_service_type,
                        'NumberOfPieces' => $qty,
                        'DescriptionOfGoods' => $descriptionOfGoods,
                        'GoodsOriginCountry' => Configuration::get('ARAMEX_COUNTRY'),
                        'Items' => '1',
                    );

                    $params['Details']['CashOnDeliveryAmount'] = array(
                        'Value' => $order->total_paid,
                        'CurrencyCode' => $aramex_shipment_currency_code
                    );

                    $params['Details']['CustomsValueAmount'] = array(
                        'Value' => $aramex_shipment_info_custom_amount,
                        'CurrencyCode' => $aramex_shipment_currency_code
                    );
                    $major_par= array();
                    $major_par['Shipments'][] = $params;
                    $info = $this->helper->getInfo();
                    $major_par['ClientInfo'] = $info['clientInfo'];
                    $report_id = trim(Configuration::get('ARAMEX_REPORT_ID'));
                    if ($report_id == "") {
                        $report_id = 9729;
                    }
                    $major_par['LabelInfo'] = array(
                        'ReportID' => $report_id,
                        'ReportType' => 'URL'
                    );
                    $replay = $this->postAction($major_par, $order, $orderItem['method'], $customer, $address);

                    if ($replay[0] == "DOM") {
                        $method = "Domestic Product Group";
                    } else {
                        $method = "International Product Group";
                    };

                    if ($replay[1] == "error") {
                        $responce .= "<p class='aramex_red'> Aramex Shipment Number - " . $orderItem['order_id'] .
                            " not created. (" . $method . ")</p>";
                        break;
                    } else {
                        $responce .= "<p class='aramex_green'> Aramex Shipment Number: " . $orderItem['order_id'] .
                            ' has been created.(' . $method . ')</p>';
                    }
                }
                echo json_encode(array('message' => $responce));
                die();
            } else {
                $errors = "<p class='aramex_red'>No orders with 'Pending' status selected.</p>";
                return json_encode(array('Test-Message' => $errors));
            }
        }
    }

    /**
     * Send request to Aramex server in order to make shipment
     *
     * @param array $major_par Data for Shipment calculation
     * @param object $order Order object
     * @param string $method Shipping method
     * @param object $customer Customer info
     * @param object $address Customers address
     * @return array Result from Aramex server
     */
    private function postAction($major_par, $order, $method, $customer, $address)
    {
        $shipper_name = $address->firstname . " " . $address->lastname;
        $info = $this->helper->getInfo();

        //SOAP object
        $soapClient = new SoapClient($info['baseUrl'] . 'shipping.wsdl', array('soap_version' => SOAP_1_1));
        try {
            //create shipment call
            $auth_call = $soapClient->CreateShipments($major_par);
            if ($auth_call->HasErrors) {
                if (empty($auth_call->Shipments)) {
                    if (count($auth_call->Notifications->Notification) > 1) {
                        foreach ($auth_call->Notifications->Notification as $notify_error) {
                            $this->aramex_errors['error'] = 'Aramex: ' . $notify_error->Code . ' - ' .
                                $notify_error->Message;
                        }
                    } else {
                        $this->aramex_errors['error'] = 'Aramex: ' . $auth_call->Notifications->Notification->Code .
                            ' - ' . $auth_call->Notifications->Notification->Message;
                    }
                } else {
                    if (count($auth_call->Shipments->ProcessedShipment->Notifications->Notification) > 1) {
                        $notification_string = '';
                        foreach ($auth_call->Shipments->ProcessedShipment->Notifications->Notification as
                                 $notification_error) {
                            $notification_string .= $notification_error->Code . ' - ' . $notification_error->Message .
                                ' <br />';
                        }
                        $this->aramex_errors['error'] = $notification_string;
                    } else {
                        $this->aramex_errors['error'] = 'Aramex: ' . $auth_call->Shipments->ProcessedShipment->
                            Notifications->Notification->Code . ' - ' . $auth_call->Shipments->ProcessedShipment->
                            Notifications->Notification->Message;
                    }
                }
                return array($method, 'error');
            } else {
                //update note
                $comment_content = "AWB No. " . $auth_call->Shipments->ProcessedShipment->ID . " - Order No. " .
                    $auth_call->Shipments->ProcessedShipment->Reference1;
                $this->helper->saveNote($order->id, $comment_content);

                //update status
                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->changeIdOrderState(3, (int)($order->id));

                /* sending mail */
                $message_body = sprintf('<p>Dear <b>%s</b> </p>', $shipper_name);
                $message_body .= sprintf('<p>Your order is #%s </p>', $auth_call->Shipments->
                ProcessedShipment->Reference1);
                $message_body .= sprintf('<p>Created Airway bill number: %s </p>', $auth_call->Shipments->
                ProcessedShipment->ID);
                $message_body .= '<p>You can track shipment on <a href="http://www.aramex.com/express/track.aspx">
http://www.aramex.com/express/track.aspx</a> </p>';
                $message_body .= '<p>If you have any questions, please feel free to contact us <b>' .
                    Configuration::get('ARAMEX_EMAIL') . '</b> </p>';

                if (Tools::getValue('aramex_email_customer') == 'yes') {
                    $to = array();
                    $bcc = array();
                    $to[] = $customer->email;
                    $copyTo = Configuration::get('ARAMEX_COPY_TO');
                    if (Configuration::get('ARAMEX_COPY_METHOD') == "0" &&
                        trim(Configuration::get('ARAMEX_COPY_TO')) != "") {
                        $emails = explode(',', $copyTo);
                        foreach ((array)$emails as $email) {
                            $bcc[] = $email;
                        }
                    }
                    if (Configuration::get('ARAMEX_COPY_METHOD') == "1" &&
                        trim(Configuration::get('ARAMEX_COPY_TO')) != "") {
                        $emails = explode(',', $copyTo);
                        foreach ((array)$emails as $email) {
                            $to[] = $email;
                        }
                        $bcc = null;
                    }
                    try {
                        Mail::Send(
                            Configuration::get('PS_LANG_DEFAULT'),
                            'contact',
                            sprintf('Aramex shipment #%s created', $order->id, $message_body),
                            array(
                                '{email}' => Configuration::get('ARAMEX_EMAIL_ORIGIN'),
                                '{lastname}' => $customer->lastname,
                                '{firstname}' => $customer->firstname,
                                '{message}' => $message_body // email content
                            ),
                            $to, // receiver email address
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $bcc
                        );
                    } catch (Exception $ex) {
                        $this->aramex_errors['error'] = $ex->getMessage();
                    }
                }
                return array($method, 'success');
            }
        } catch (Exception $e) {
            $this->aramex_errors['error'] = $e->getMessage();
            return array($method, 'error');
        }
    }
}
