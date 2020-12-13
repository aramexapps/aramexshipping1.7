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
 * Controller for Shipment functionality
 */
class AdminShipmentController extends AdminController
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
        }
    }

    /**
     * Starting method
     *
     * @return  array
     */
    public function initContent()
    {

        if (Tools::getValue('aramex_shipment_shipper_account_show') == 1) {
            $info = $this->helper->getInfo();
        } else {
            $info = $this->helper->getInfoCod();
        }

        //SOAP object
        $soapClient = new SoapClient($info['baseUrl'] . 'shipping.wsdl', array('soap_version' => SOAP_1_1));
        $aramex_errors = false;
        try {
            /* here's your form processing */
            $order = new Order(Tools::getValue('aramex_shipment_original_reference'));
            $items = $order->getProductsDetail();
            $descriptionOfGoods = '';
            foreach ($items as $itemvv) {
                $descriptionOfGoods .= $itemvv['product_id'] . ' - ' . trim($itemvv['product_name'] . ' ');
            }
            $descriptionOfGoods = Tools::substr(trim($descriptionOfGoods), 0, 100);

            $totalItems = (trim(Tools::getValue('number_pieces')) == '') ? 1 :
            (int)Tools::getValue('number_pieces');
            $aramex_atachments = array();
            //attachment
            for ($i = 1; $i <= 3; $i++) {
                $fileName = $_FILES['file' . $i]['name'];
                if (isset($fileName) != '') {
                    $fileName = explode('.', $fileName);
                    $fileName = $fileName[0]; //filename without extension
                    $fileData = '';
                    if ($_FILES['file' . $i]['tmp_name'] != '') {
                        $fileData = Tools::file_get_contents($_FILES['file' . $i]['tmp_name']);
                    }
                    $ext = pathinfo($_FILES['file' . $i]['name'], PATHINFO_EXTENSION); //file extension
                    if ($fileName && $ext && $fileData) {
                        $aramex_atachments[] = array(
                            'FileName' => $fileName,
                            'FileExtension' => $ext,
                            'FileContents' => $fileData
                        );
                    }
                }
            }

            $totalWeight = Tools::getValue('order_weight');
            $params = array();
            if (Tools::getValue('aramex_shipment_shipper_account_show') == 1) {
                $AccountNumber_1 = (Tools::getValue('aramex_shipment_info_billing_account') == 1) ?
                Tools::getValue('aramex_shipment_shipper_account') :
                    Tools::getValue('aramex_shipment_shipper_account');
                $AccountPin_1 = (Tools::getValue('aramex_shipment_info_billing_account') == 1) ?
                Tools::getValue('aramex_shipment_shipper_account_pin') :
                    Tools::getValue('aramex_shipment_shipper_account_pin');
                $AccountNumber_2 = (Tools::getValue('aramex_shipment_info_billing_account') == 2) ?
                    Tools::getValue('aramex_shipment_shipper_account') : '';
                $AccountPin_2 = (Tools::getValue('aramex_shipment_info_billing_account') == 2) ?
                    Tools::getValue('aramex_shipment_shipper_account_pin') : '';
                $AccountNumber_3 = Tools::getValue('aramex_shipment_shipper_account');
                $AccountPin_3 = Tools::getValue('aramex_shipment_shipper_account_pin');
            } else {
                $AccountNumber_1 = (Tools::getValue('aramex_shipment_info_billing_account') == 1) ?
                    Tools::getValue('aramex_shipment_shipper_account_cod') :
                    Tools::getValue('aramex_shipment_shipper_account_cod');
                $AccountPin_1 = (Tools::getValue('aramex_shipment_info_billing_account') == 1) ?
                    Tools::getValue('aramex_shipment_shipper_account_pin_cod') :
                    Tools::getValue('aramex_shipment_shipper_account_pin_cod');
                $AccountNumber_2 = (Tools::getValue('aramex_shipment_info_billing_account') == 2) ?
                    Tools::getValue('aramex_shipment_shipper_account_cod') : '';
                $AccountPin_2 = (Tools::getValue('aramex_shipment_info_billing_account') == 2) ?
                    Tools::getValue('aramex_shipment_shipper_account_pin_cod') : '';
                $AccountNumber_3 = Tools::getValue('aramex_shipment_shipper_account_cod');
                $AccountPin_3 = Tools::getValue('aramex_shipment_shipper_account_pin_cod');
            }
            //shipper parameters
            $params['Shipper'] = array(
                'Reference1' => Tools::getValue('aramex_shipment_shipper_reference'), //'ref11111',
                'Reference2' => '',
                'AccountNumber' => $AccountNumber_1,
                'AccountPin' => $AccountPin_1,
                //Party Address
                'PartyAddress' => array(
                    'Line1' => addslashes(Tools::getValue('aramex_shipment_shipper_street')), //'13 Mecca St',
                    'Line2' => '',
                    'Line3' => '',
                    'City' => Tools::getValue('aramex_shipment_shipper_city'), //'Dubai',
                    'StateOrProvinceCode' => Tools::getValue('aramex_shipment_shipper_state'), //'',
                    'PostCode' => Tools::getValue('aramex_shipment_shipper_postal'),
                    'CountryCode' => Tools::getValue('aramex_shipment_shipper_country'), //'AE'
                ),
                //Contact Info
                'Contact' => array(
                    'Department' => '',
                    'PersonName' => Tools::getValue('aramex_shipment_shipper_name'), //'Suheir',
                    'Title' => '',
                    'CompanyName' => Tools::getValue('aramex_shipment_shipper_company'), //'Aramex',
                    'PhoneNumber1' => Tools::getValue('aramex_shipment_shipper_phone'), //'55555555',
                    'PhoneNumber1Ext' => '',
                    'PhoneNumber2' => '',
                    'PhoneNumber2Ext' => '',
                    'FaxNumber' => '',
                    'CellPhone' => Tools::getValue('aramex_shipment_shipper_phone'),
                    'EmailAddress' => Tools::getValue('aramex_shipment_shipper_email'), //'',
                    'Type' => ''
                ),
            );
            //consinee parameters
            $params['Consignee'] = array(
                'Reference1' => Tools::getValue('aramex_shipment_receiver_reference'), //'',
                'Reference2' => '',
                'AccountNumber' => $AccountNumber_2,
                'AccountPin' => $AccountPin_2,
                //Party Address
                'PartyAddress' => array(
                    'Line1' => Tools::getValue('aramex_shipment_receiver_street'), //'15 ABC St',
                    'Line2' => '',
                    'Line3' => '',
                    'City' => Tools::getValue('aramex_shipment_receiver_city'), //'Amman',
                    'StateOrProvinceCode' => '',
                    'PostCode' => Tools::getValue('aramex_shipment_receiver_postal'),
                    'CountryCode' => Tools::getValue('aramex_shipment_receiver_country'), //'JO'
                ),
                //Contact Info
                'Contact' => array(
                    'Department' => '',
                    'PersonName' => Tools::getValue('aramex_shipment_receiver_name'), //'Mazen',
                    'Title' => '',
                    'CompanyName' => Tools::getValue('aramex_shipment_receiver_company'), //'Aramex',
                    'PhoneNumber1' => Tools::getValue('aramex_shipment_receiver_phone'), //'6666666',
                    'PhoneNumber1Ext' => '',
                    'PhoneNumber2' => '',
                    'PhoneNumber2Ext' => '',
                    'FaxNumber' => '',
                    'CellPhone' => Tools::getValue('aramex_shipment_receiver_phone'),
                    'EmailAddress' => Tools::getValue('aramex_shipment_receiver_email'), //'mazen@aramex.com',
                    'Type' => ''
                )
            );
            //new
            if (Tools::getValue('aramex_shipment_info_billing_account') == 3) {
                $params['ThirdParty'] = array(
                    'Reference1' => Tools::getValue('aramex_shipment_shipper_reference'), //'ref11111',
                    'Reference2' => '',
                    'AccountNumber' => $AccountNumber_3,
                    'AccountPin' => $AccountPin_3,
                    //Party Address
                    'PartyAddress' => array(
                        'Line1' => $info['clientInfo']['address'],
                        'Line2' => '',
                        'Line3' => '',
                        'City' => $info['clientInfo']['city'],
                        'StateOrProvinceCode' => $info['clientInfo']['state'],
                        'PostCode' => $info['clientInfo']['postalcode'],
                        'CountryCode' => $info['clientInfo']['country'],
                    ),
                    //Contact Info
                    'Contact' => array(
                        'Department' => '',
                        'PersonName' => $info['clientInfo']['name'],
                        'Title' => '',
                        'CompanyName' => $info['clientInfo']['company'],
                        'PhoneNumber1' => $info['clientInfo']['phone'],
                        'PhoneNumber1Ext' => '',
                        'PhoneNumber2' => '',
                        'PhoneNumber2Ext' => '',
                        'FaxNumber' => '',
                        'CellPhone' => $info['clientInfo']['phone'],
                        'EmailAddress' => $info['clientInfo']['email'],
                        'Type' => ''
                    ),
                );
            }

            ////// add COD
            $services = array();
            if (Tools::getValue('aramex_shipment_info_product_type') == "CDA") {
                if (Tools::getValue('aramex_shipment_info_service_type') === null) {
                    array_push($services, "CODS");
                } elseif (!in_array("CODS", Tools::getValue('aramex_shipment_info_service_type'))) {
                    $services = array_merge($services, Tools::getValue('aramex_shipment_info_service_type'));
                    array_push($services, "CODS");
                } else {
                    $services = array_merge($services, Tools::getValue('aramex_shipment_info_service_type'));
                }
            } else {
                if (Tools::getValue('aramex_shipment_info_service_type') == null) {
                    #Tools::getValue('aramex_shipment_info_service_type') = array();
                } else {
                    $services = array_merge($services, Tools::getValue('aramex_shipment_info_service_type'));
                }
            }

            $services = implode(',', $services);

            ///// add COD end
            // Other Main Shipment Parameters
            $params['Reference1'] = Tools::getValue('aramex_shipment_info_reference'); //'Shpt0001';
            $params['Reference2'] = '';
            $params['Reference3'] = '';
            $params['ForeignHAWB'] = Tools::getValue('aramex_shipment_info_foreignhawb');
            $params['TransportType'] = 0;
            $params['ShippingDateTime'] = time();
            $params['DueDate'] = time() + (7 * 24 * 60 * 60);
            $params['PickupLocation'] = 'Reception';
            $params['PickupGUID'] = '';
            $params['Comments'] = Tools::getValue('aramex_shipment_info_comment');
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
                    'Value' => $totalWeight,
                    'Unit' => Tools::getValue('weight_unit')
                ),
                'ProductGroup' => Tools::getValue('aramex_shipment_info_product_group'), //'EXP',
                'ProductType' => Tools::getValue('aramex_shipment_info_product_type'), //,'PDX'
                'PaymentType' => Tools::getValue('aramex_shipment_info_payment_type'),
                'PaymentOptions' => Tools::getValue('aramex_shipment_info_payment_option'), //$post['aramex_shipment_info_payment_option']
                'Services' => $services,
                'NumberOfPieces' => $totalItems,
                'DescriptionOfGoods' => (trim(Tools::getValue('aramex_shipment_description')) == '') ?
                    $descriptionOfGoods : Tools::substr(trim(Tools::getValue('aramex_shipment_description')), 0, 100),
                'GoodsOriginCountry' => Tools::getValue('aramex_shipment_shipper_country'), //'JO',
                'Items' => $totalItems,
            );
            if (count($aramex_atachments)) {
                $params['Attachments'] = $aramex_atachments;
            }

            $params['Details']['CashOnDeliveryAmount'] = array(
                'Value' => Tools::getValue('aramex_shipment_info_cod_amount'),
                'CurrencyCode' => Tools::getValue('aramex_shipment_currency_code')
            );

            $params['Details']['CustomsValueAmount'] = array(
                'Value' => Tools::getValue('aramex_shipment_info_custom_amount'),
                'CurrencyCode' => Tools::getValue('aramex_shipment_currency_code')
            );
            $major_par = array();
            $major_par['Shipments'][] = $params;
            $major_par['ClientInfo'] = $info['clientInfo'];
            $report_id = (int)$info['clientInfo']['report_id'];
            if (!$report_id) {
                $report_id = 9729;
            }
            $major_par['LabelInfo'] = array(
                'ReportID' => $report_id,
                'ReportType' => 'URL'
            );

            $_SESSION['form_data'] = Tools::getAllValues();
            // used for tracking error messages
            $aramex_errors = array();
                            $id_customer = $order->id_customer;
                            $customer = new Customer((int)$id_customer);
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
                //create shipment call
                $auth_call = $soapClient->CreateShipments($major_par);
                if ($auth_call->HasErrors) {
                    if (empty($auth_call->Shipments)) {
                        if (count($auth_call->Notifications->Notification) > 1) {
                            foreach ($auth_call->Notifications->Notification as $notify_error) {
                                $aramex_errors['error'] = 'Aramex: ' . $notify_error->Code . ' - ' .
                                    $notify_error->Message;
                            }
                        } else {
                            $aramex_errors['error'] = 'Aramex: ' . $auth_call->Notifications->Notification->Code .
                                ' - ' . $auth_call->Notifications->Notification->Message;
                        }
                    } else {
                        if (count($auth_call->Shipments->ProcessedShipment->Notifications->Notification) > 1) {
                            $notification_string = '';
                            foreach ($auth_call->Shipments->ProcessedShipment->Notifications->Notification as
                                     $notification_error) {
                                $notification_string .= $notification_error->Code . ' - ' . $notification_error->Message
                                    . "\r\n";
                            }
                            $aramex_errors['error'] = $notification_string;
                        } else {
                            $aramex_errors['error'] = 'Aramex: ' . $auth_call->Shipments->ProcessedShipment->
                                Notifications->Notification->Code . ' - ' . $auth_call->Shipments->ProcessedShipment->
                                Notifications->Notification->Message;
                        }
                    }
                    $_SESSION['aramex_errors'] = $aramex_errors;
                    Tools::redirect(
                        Tools::getProtocol() . rtrim(
                            Tools::getValue('aramex_shipment_referer'),
                            '&aramexpopup/show'
                        ) . '&aramexpopup/show'
                    );
                    exit;
                } else {
                    if (Tools::getValue('aramex_return_shipment_creation_date') == "create") {
                        //update note
                        $comment_content = "AWB No. " . $auth_call->Shipments->ProcessedShipment->ID . " - Order No. "
                            . $auth_call->Shipments->ProcessedShipment->Reference1;
                        $this->helper->saveNote($order->id, $comment_content);

                        //update status
                        $history = new OrderHistory();
                        $history->id_order = (int)$order->id;
                        $history->changeIdOrderState(3, (int)($order->id));

                        /* sending mail */
                        $message_body = sprintf(
                            '<p>Dear <b>%s,</b> </p>',
                            Tools::getValue('aramex_shipment_receiver_name')
                        );
                        $message_body .= sprintf('<p>Your order is #%s </p>', $auth_call->Shipments->
                        ProcessedShipment->Reference1);
                        $message_body .= sprintf('<p>Created Airway bill number: %s </p>', $auth_call->
                        Shipments->ProcessedShipment->ID);
                        $message_body .= '<p>You can track shipment on 
<a href="http://www.aramex.com/express/track.aspx">http://www.aramex.com/express/track.aspx</a> </p>';
                        $message_body .= '<p>If you have any questions, please feel free to contact us.</p>';

                        if (Tools::getValue('aramex_email_customer') == 'yes') {
                            $id_customer = $order->id_customer;
                            $customer = new Customer((int)$id_customer);
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
                                    (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                                    'contact', // email template file to be use
                                    sprintf('Aramex shipment #%s created', $order->id, $message_body), // email subject
                                    array(
                                        '{email}' => Configuration::get('ARAMEX_EMAIL_ORIGIN'), // sender email address
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
                                $aramex_errors['error'] = $ex->getMessage();
                            }
                        }

                        $aramex_errors['success'] = 'Aramex Shipment Number: ' . $auth_call->Shipments->
                            ProcessedShipment->ID . ' has been created.';
                    } elseif (Tools::getValue('aramex_return_shipment_creation_date') == "return") {
                        $aramex_errors['success'] = 'Aramex Shipment Return Order Number: ' . $auth_call->
                            Shipments->ProcessedShipment->ID . ' has been created.';
                        $comment_content = "Aramex Shipment Return Order AWB No. " . $auth_call->Shipments->
                            ProcessedShipment->ID . " - Order No. " . $auth_call->Shipments->
                            ProcessedShipment->Reference1;
                        $this->helper->saveNote($order->id, $comment_content);
                    } else {
                        $aramex_errors['error'] = 'Cannot do shipment for the order.';
                    }
                }
            } catch (Exception $e) {
                $aramex_errors = true;
                $aramex_errors['error'] = $e->getMessage();
            }

            if (count($aramex_errors['error']) > 0) {
                $_SESSION['aramex_errors'] = $aramex_errors;
                Tools::redirect(
                    Tools::getProtocol() . rtrim(
                        Tools::getValue('aramex_shipment_referer'),
                        '&aramexpopup/show'
                    ) . '&aramexpopup/show'
                );
                exit;
            } else {
                //success exit
                $_SESSION['aramex_errors'] = $aramex_errors;
                Tools::redirect(
                    Tools::getProtocol() . rtrim(
                        Tools::getValue('aramex_shipment_referer'),
                        '&aramexpopup/show'
                    ) . '&aramexpopup/show'
                );
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['aramex_errors'] = $aramex_errors;
            Tools::redirect(
                Tools::getProtocol() . rtrim(
                    Tools::getValue('aramex_shipment_referer'),
                    '&aramexpopup/show'
                ) . '&aramexpopup/show'
            );
            exit;
        }
    }
}
