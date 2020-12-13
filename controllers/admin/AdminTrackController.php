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
 * Controller for Tracking functionality
 */
class AdminTrackController extends AdminController
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
     * @return  array
     */
    public function initContent()
    {
        if ($this->ajax == true) {
            $info = $this->helper->getInfo();
            $trackingvalue = Tools::getValue("aramex-track");
            $response = array();

            //SOAP object
            $soapClient = new SoapClient($info['baseUrl'] . 'Tracking.wsdl', array('soap_version' => SOAP_1_1));
            $aramexParams = $this->getAuthDetails($info);
            $aramexParams['Transaction'] = array('Reference1' => '001');
            $aramexParams['Shipments'] = array($trackingvalue);
            $_resAramex = $soapClient->TrackShipments($aramexParams);

            if (is_object($_resAramex) && !$_resAramex->HasErrors) {
                $response['type'] = 'success';
                if (!empty($_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->
                Value->TrackingResult)) {
                    $response['html'] = $this->getTrackingInfoTable($_resAramex->TrackingResults->
                    KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult);
                } else {
                    $response['html'] = 'Unable to retrieve quotes, please check if the Tracking Number is
                     valid or contact your administrator.';
                }
            } else {
                $response['type'] = 'error';
                foreach ($_resAramex->Notifications as $notification) {
                    $response['html'] .= '<b>' . $notification->Code . '</b>' . $notification->Message;
                }
            }
            print json_encode($response);
            die();
        }
    }
    /**
     * Gets Client info from array
     * @param $info array Client info
     * @return array Client info
     */
    private function getAuthDetails($info)
    {
        return array(
            'ClientInfo' => $info['clientInfo']
        );
    }

    /**
     * Creates HTML code for tracking table
     *
     * @param $HAWBHistory array
     * @return string
     */
    private function getTrackingInfoTable($HAWBHistory)
    {
        $checkArray = is_array($HAWBHistory);
        $_resultTable = '<table summary="Item Tracking"  class="data-table">';
        $_resultTable .= '<col width="1">
                          <col width="1">
                          <col width="1">
                          <col width="1">
                          <thead>
                          <tr class="first last">
                          <th>Location</th>
                          <th>Action Date/Time</th>
                          <th class="a-right">Tracking Description</th>
                          <th class="a-center">Comments</th>
                          </tr>
                          </thead><tbody>';
        if ($checkArray) {
            foreach ($HAWBHistory as $HAWBUpdate) {
                $_resultTable .= '<tr>
                    <td>' . $HAWBUpdate->UpdateLocation . '</td>
                    <td>' . $HAWBUpdate->UpdateDateTime . '</td>
                    <td>' . $HAWBUpdate->UpdateDescription . '</td>
                    <td>' . $HAWBUpdate->Comments . '</td>
                    </tr>';
            }
        } else {
            $_resultTable .= '<tr>
                    <td>' . $HAWBHistory->UpdateLocation . '</td>
                    <td>' . $HAWBHistory->UpdateDateTime . '</td>
                    <td>' . $HAWBHistory->UpdateDescription . '</td>
                    <td>' . $HAWBHistory->Comments . '</td>
                    </tr>';
        }
        $_resultTable .= '</tbody></table>';
        return $_resultTable;
    }
}
