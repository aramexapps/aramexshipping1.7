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

/**
 * Class AramexSerchautocitiesMethodModel is a model for Serch sities functionality
 */
class AramexSerchautocitiesMethodModel extends AramexHelper
{
    /**
     * Get cities
     *
     * @param string $CountryCode Name of country
     * @param null|string $NameStartsWith Name of city
     * @param null|string $code Code of city
     * @return array List of cities
     */
    public function fetchCities($CountryCode, $NameStartsWith = null, $code = false)
    {
        if ($code == false) {
            $CountryCode = Country::getIsoById($CountryCode);
        }

        $info = $this->getInfo();
        $params = array(
            'ClientInfo' => $info['clientInfo'],
            'Transaction' => array(
                'Reference1' => '001',
                'Reference2' => '002',
                'Reference3' => '003',
                'Reference4' => '004',
                'Reference5' => '005'
            ),
            'CountryCode' => $CountryCode,
            'State' => null,
            'NameStartsWith' => $NameStartsWith,
        );

        //SOAP object
        $soapClient = new SoapClient($info['baseUrl'] . 'Location-API-WSDL.wsdl', array('soap_version' => SOAP_1_1));
        try {
            $results = $soapClient->FetchCities($params);
            if (is_object($results)) {
                if (!$results->HasErrors) {
                    $cities = isset($results->Cities->string) ? $results->Cities->string : false;
                    return $cities;
                }
            }
        } catch (SoapFault $fault) {
            die('Error : ' . $fault->faultstring);
        }
    }

    /**
     * Validate address
     *
     * @param array $address Address
     * @return array Result from Aramex server
     */
    public function validateAddress($address)
    {
        $info = $this->getInfo();
        $params = array(
            'ClientInfo' => $info['clientInfo'],
            'Transaction' => array(
                'Reference1' => '001',
                'Reference2' => '002',
                'Reference3' => '003',
                'Reference4' => '004',
                'Reference5' => '005'
            ),
            'Address' => array(
                'Line1' => '001',
                'Line2' => '',
                'Line3' => '',
                'City' => $address['city'],
                'StateOrProvinceCode' => '',
                'PostCode' => $address['post_code'],
                'CountryCode' => $address['country_code']
            )
        );

        //SOAP object
        $soapClient = new SoapClient($info['baseUrl'] . 'Location-API-WSDL.wsdl', array('soap_version' => SOAP_1_1));
        $reponse = array();
        try {
            $results = $soapClient->ValidateAddress($params);
            if (is_object($results)) {
                if ($results->HasErrors) {
                    $suggestedAddresses = (isset($results->SuggestedAddresses->Address)) ?
                        $results->SuggestedAddresses->Address : "";
                    $message = $results->Notifications->Notification->Message;
                    $reponse = array('is_valid' => false, 'suggestedAddresses' =>
                        $suggestedAddresses, 'message' => $message);
                } else {
                    $reponse = array('is_valid' => true);
                }
            }
        } catch (SoapFault $fault) {
            die('Error : ' . $fault->faultstring);
        }
        return $reponse;
    }
}
