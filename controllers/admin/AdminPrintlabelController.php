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
class AdminPrintlabelController extends AdminController
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
            if (Tools::getValue('aramex-printlabel')) {
                //SOAP object
                $soapClient = new SoapClient(
                    $info['baseUrl'] . 'shipping.wsdl',
                    array(
                        'soap_version' => SOAP_1_1
                    )
                );
                if (Tools::getValue('aramex-lasttrack')) {
                    $report_id = $info['clientInfo']['report_id'];
                    if (!$report_id) {
                        $report_id = 9729;
                    }
                    $params = array(
                        'ClientInfo' => $info['clientInfo'],
                        'Transaction' => array(
                            'Reference1' => Tools::getValue('aramex-printlabel'),
                            'Reference2' => '',
                            'Reference3' => '',
                            'Reference4' => '',
                            'Reference5' => '',
                        ),
                        'LabelInfo' => array(
                            'ReportID' => $report_id,
                            'ReportType' => 'URL',
                        ),
                    );
                    $params['ShipmentNumber'] = Tools::getValue('aramex-lasttrack');
                    $aramex_errors = array();
                    try {
                        $auth_call = $soapClient->PrintLabel($params);
                        /* bof  PDF demaged Fixes debug */
                        if ($auth_call->HasErrors) {
                            if (count($auth_call->Notifications->Notification) > 1) {
                                foreach ($auth_call->Notifications->Notification as $notify_error) {
                                    $error = "";
                                    $error .= 'Aramex: ' . $notify_error->Code . ' - ' . $notify_error->Message;
                                    $aramex_errors['error'] = $error;
                                }
                            } else {
                                $aramex_errors['error'] = 'Aramex: ' . $auth_call->Notifications->Notification->
                                    Code . ' - ' . $auth_call->Notifications->Notification->Message;
                            }
                            $_SESSION['aramex_errors_printlabel'] = $aramex_errors;
                            Tools::redirect(
                                Tools::getProtocol() . rtrim(
                                    Tools::getValue('aramex_shipment_referer'),
                                    '&aramexpopup/show_printlabel'
                                ) . '&aramexpopup/show_printlabel'
                            );
                            exit();
                        }
                        /* eof  PDF demaged Fixes */
                        $filepath = $auth_call->ShipmentLabel->LabelURL;
                        # Tools::redirect("HTTP/1.1 301 Moved Permanently");
                        Tools::redirect($filepath);
                        exit();
                    } catch (SoapFault $fault) {
                        $aramex_errors['error'] = $fault->faultstring;
                        $_SESSION['aramex_errors_printlabel'] = $aramex_errors;
                        Tools::redirect(
                            Tools::getProtocol() . rtrim(
                                Tools::getValue('aramex_shipment_referer'),
                                '&aramexpopup/show_printlabel'
                            ) . '&aramexpopup/show_printlabel'
                        );
                        exit();
                    } catch (Exception $e) {
                        $aramex_errors['error'] = $e->getMessage();
                        $_SESSION['aramex_errors_printlabel'] = $aramex_errors;
                        Tools::redirect(
                            Tools::getProtocol() . rtrim(
                                Tools::getValue('aramex_shipment_referer'),
                                '&aramexpopup/show_printlabel'
                            ) . '&aramexpopup/show_printlabel'
                        );
                        exit();
                    }
                } else {
                    $aramex_errors['error'] = 'Shipment is empty or not created yet.';
                    $_SESSION['aramex_errors_printlabel'] = $aramex_errors;
                    Tools::redirect(
                        Tools::getProtocol() . rtrim(
                            Tools::getValue('aramex_shipment_referer'),
                            '&aramexpopup/show_printlabel'
                        ) . '&aramexpopup/show_printlabel'
                    );
                    exit();
                }
            } else {
                $aramex_errors['error'] = 'This order no longer exists.';
                $_SESSION['aramex_errors_printlabel'] = $aramex_errors;
                Tools::redirect(
                    Tools::getProtocol() . rtrim(
                        Tools::getValue('aramex_shipment_referer'),
                        '&aramexpopup/show_printlabel'
                    ) . '&aramexpopup/show_printlabel'
                );
                exit();
            }
        }
    }
}
