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
 * Class DataAramexSettings is for getting Admin settings
 */
class DataAramexSettings
{
    /**
     * @var array $config Settings
     */
    private $config = array(
        'allowed_domestic_methods' => array(
            array(
                'id_option' => 'BLK',
                'name' => 'Special: Bulk Mail Delivery'
            ),
            array(
                'id_option' => 'BLT',
                'name' => 'Domestic - Bullet Delivery'
            ),
            array(
                'id_option' => 'CDA',
                'name' => 'Special Delivery'
            ),
            array(
                'id_option' => 'CDS',
                'name' => 'Special: Credit Cards Delivery'
            ),
            array(
                'id_option' => 'CGO',
                'name' => 'Air Cargo (India)'
            ),
            array(
                'id_option' => 'COM',
                'name' => 'Special: Cheque Collection'
            ),
            array(
                'id_option' => 'DEC',
                'name' => 'Special: Invoice Delivery'
            ),
            array(
                'id_option' => 'EMD',
                'name' => 'Early Morning delivery'
            ),
            array(
                'id_option' => 'FIX',
                'name' => 'Special: Bank Branches Run'
            ),
            array(
                'id_option' => 'LGS',
                'name' => 'Logistic Shipment'
            ),
            array(
                'id_option' => 'OND',
                'name' => 'Overnight (Document)'
            ),
            array(
                'id_option' => 'ONP',
                'name' => 'Overnight (Parcel)'
            ),
            array(
                'id_option' => 'P24',
                'name' => 'Road Freight 24 hours service'
            ),
            array(
                'id_option' => 'P48',
                'name' => 'Road Freight 48 hours service'
            ),
            array(
                'id_option' => 'PEC',
                'name' => 'Economy Delivery'
            ),
            array(
                'id_option' => 'PEX',
                'name' => 'Road Express'
            ),
            array(
                'id_option' => 'SFC',
                'name' => 'Surface  Cargo (India)'
            ),
            array(
                'id_option' => 'SMD',
                'name' => 'Same Day (Document)'
            ),
            array(
                'id_option' => 'SMP',
                'name' => 'Same Day (Parcel)'
            ),
            array(
                'id_option' => 'SPD',
                'name' => 'Special: Legal Branches Mail Service'
            ),
            array(
                'id_option' => 'SPL',
                'name' => 'Special : Legal Notifications Delivery'
            ),
        ),
        'allowed_domestic_additional_services' => array(
            array(
                'id_option' => 'AM10',
                'name' => 'Morning delivery'
            ),
            array(
                'id_option' => 'CHST',
                'name' => 'Chain Stores Delivery'
            ),
            array(
                'id_option' => 'CODS',
                'name' => 'Cash On Delivery Service'
            ),
            array(
                'id_option' => 'COMM',
                'name' => 'Commercial'
            ),
            array(
                'id_option' => 'CRDT',
                'name' => 'Credit Card'
            ),
            array(
                'id_option' => 'DDP',
                'name' => 'DDP - Delivery Duty Paid - For European Use'
            ),
            array(
                'id_option' => 'DDU',
                'name' => 'DDU - Delivery Duty Unpaid - For the European Freight'
            ),
            array(
                'id_option' => 'EXW',
                'name' => 'Not An Aramex Customer - For European Freight'
            ),
            array(
                'id_option' => 'INSR',
                'name' => 'Insurance'
            ),
            array(
                'id_option' => 'RTRN',
                'name' => 'Return'
            ),
            array(
                'id_option' => 'SPCL',
                'name' => 'Special Services'
            ),
        ),
        'allowed_international_methods' => array(
            array(
                'id_option' => 'DPX',
                'name' => 'Value Express Parcels'
            ),
            array(
                'id_option' => 'EDX',
                'name' => 'Economy Document Express'
            ),
            array(
                'id_option' => 'EPX',
                'name' => 'Economy Parcel Express'
            ),
            array(
                'id_option' => 'GDX',
                'name' => 'Ground Document Express'
            ),
            array(
                'id_option' => 'GPX',
                'name' => 'Ground Parcel Express'
            ),
            array(
                'id_option' => 'IBD',
                'name' => 'International defered'
            ),
            array(
                'id_option' => 'PDX',
                'name' => 'Priority Document Express'
            ),
            array(
                'id_option' => 'PLX',
                'name' => 'Priority Letter Express ( less than 0.5 kg Docs)'
            ),
            array(
                'id_option' => 'PPX',
                'name' => 'Priority Parcel Express'
            ),
        ),
        'allowed_international_additional_services' => array(
            array(
                'id_option' => 'AM10',
                'name' => 'Morning delivery'
            ),
            array(
                'id_option' => 'CODS',
                'name' => 'Cash On Delivery'
            ),
            array(
                'id_option' => 'CSTM',
                'name' => 'CSTM'
            ),
            array(
                'id_option' => 'EUCO',
                'name' => 'NULL'
            ),
            array(
                'id_option' => 'FDAC',
                'name' => 'FDAC'
            ),
            array(
                'id_option' => 'FRDM',
                'name' => 'FRDM'
            ),
            array(
                'id_option' => 'INSR',
                'name' => 'Insurance'
            ),
            array(
                'id_option' => 'NOON',
                'name' => 'Noon Delivery'
            ),
            array(
                'id_option' => 'ODDS',
                'name' => 'Over Size'
            ),
            array(
                'id_option' => 'RTRN',
                'name' => 'RTRN'
            ),
            array(
                'id_option' => 'SIGR',
                'name' => 'Signature Required'
            ),
            array(
                'id_option' => 'SPCL',
                'name' => 'Special Services'
            ),
        )
    );

    /**
     * Gets Array with Admin settings
     *
     * @return array Array with Admin settings
     */
    public function getAllElements()
    {
        return $this->config;
    }

    /**
     * Gets list of Aramex shipping methods
     *
     * @return array List of Aramex shipping methods
     */
    public function getShippingMethods()
    {
        $array = array();
        foreach ($this->config as $key => $item) {
            if ($key == 'allowed_domestic_methods') {
                foreach ($item as $item_internal) {
                    $array['Domestic'][] = $item_internal['name'];
                }
            }

            if ($key == 'allowed_international_methods') {
                foreach ($item as $item_internal) {
                    $array['International'][] = $item_internal['name'];
                }
            }
        }
        return $array;
    }
}
