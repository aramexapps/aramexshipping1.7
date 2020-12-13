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

if (!class_exists('AramexHelper')) {
    /**
     * Class Helper
     */
    class AramexHelper extends FrontControllerCore
    {
        /**
         * Get path to WSDL file
         *
         * @return string Path to WSDL file
         */
        private static function getPath()
        {
            return _PS_MODULE_DIR_ . 'aramexshipping/wsdl/';
        }

        /**
         * Get Path to WSDL file
         *
         * @return string Path to Wsdl file
         */
        private function getWsdlPath()
        {
            $sandbox_flag = Configuration::get('ARAMEX_SANDBOX_FLAG', true);
            if ($sandbox_flag == 1) {
                $path = self::getPath() . 'test/';
            } else {
                $path = self::getPath();
            }
            return $path;
        }

        /**
         * Get admin`s settings
         *
         * @return array Admin settings
         */
        private function getClientInfo()
        {
            return array(
                'AccountCountryCode' => Configuration::get('ARAMEX_ACCOUNT_COUNTRY_CODE'),
                'AccountEntity' => Configuration::get('ARAMEX_ACCOUNT_ENTITY'),
                'AccountNumber' => Configuration::get('ARAMEX_ACCOUNT_NUMBER'),
                'AccountPin' => Configuration::get('ARAMEX_ACCOUNT_PIN'),
                'UserName' => Configuration::get('ARAMEX_USER_NAME'),
                'Password' => Configuration::get('ARAMEX_PASSWORD'),
                'Version' => 'v1.0',
                'Source' => 31,
                'address' => Configuration::get('ARAMEX_ADDRESS'),
                'city' => Configuration::get('ARAMEX_CITY'),
                'state' => Configuration::get('ARAMEX_STATE'),
                'postalcode' => Configuration::get('ARAMEX_POSTALCODE'),
                'country' => Configuration::get('ARAMEX_COUNTRY'),
                'name' => Configuration::get('ARAMEX_NAME'),
                'company' => Configuration::get('ARAMEX_COMPANY'),
                'phone' => Configuration::get('ARAMEX_PHONE'),
                'email' => Configuration::get('ARAMEX_EMAIL_ORIGIN'),
                'report_id' => Configuration::get('ARAMEX_REPORT_ID'),
            );
        }

        /**
         * Get admin`s COD settings
         *
         * @return array Admin`s COD settings
         */
        private function getClientInfoCOD()
        {
            return array(
                'AccountCountryCode' => Configuration::get('ARAMEX_COD_ACCOUNT_COUNTRY_CODE'),
                'AccountEntity' => Configuration::get('ARAMEX_COD_ACCOUNT_ENTITY'),
                'AccountNumber' => Configuration::get('ARAMEX_COD_ACCOUNT_NUMBER'),
                'AccountPin' => Configuration::get('ARAMEX_COD_ACCOUNT_PIN'),
                'UserName' => Configuration::get('ARAMEX_USER_NAME'),
                'Password' => Configuration::get('ARAMEX_PASSWORD'),
                'Version' => 'v1.0',
                'Source' => 31,
                'address' => Configuration::get('ARAMEX_ADDRESS'),
                'city' => Configuration::get('ARAMEX_CITY'),
                'state' => Configuration::get('ARAMEX_STATE'),
                'postalcode' => Configuration::get('ARAMEX_POSTALCODE'),
                'country' => Configuration::get('ARAMEX_COUNTRY'),
                'name' => Configuration::get('ARAMEX_NAME'),
                'company' => Configuration::get('ARAMEX_COMPANY'),
                'phone' => Configuration::get('ARAMEX_PHONE'),
                'email' => Configuration::get('ARAMEX_EMAIL_ORIGIN'),
                'report_id' => Configuration::get('ARAMEX_REPORT_ID'),
            );
        }

        /**
         * Get admin`s Email settings
         *
         * @return array Admin`s Email settings
         */
        private function getEmailOptions()
        {
            return array(
                'copy_to' => Configuration::get('aramex_copy_to'),
                'copy_method' => Configuration::get('aramex_copy_method'),
            );
        }

        /**
         * Get info about Admin
         *
         * @return array Admin info
         */
        public function getInfo()
        {
            $baseUrl = $this->getWsdlPath();
            $clientInfo = $this->getClientInfo();
            $copyInfo = $this->getEmailOptions();
            return (array('baseUrl' => $baseUrl, 'clientInfo' => $clientInfo, '$copyInfo' => $copyInfo));
        }

        /**
         * Get info about Admin (COD)
         *
         * @return array Admin info
         */
        public function getInfoCod()
        {
            $baseUrl = $this->getWsdlPath();
            $clientInfo = $this->getClientInfoCOD();
            $copyInfo = $this->getEmailOptions();
            return (array('baseUrl' => $baseUrl, 'clientInfo' => $clientInfo, '$copyInfo' => $copyInfo));
        }

        /**
         * Save note
         *
         * @param string $id_order Order id
         * @param string $note Note
         * @return void
         */
        public function saveNote($id_order, $note)
        {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'aramex_note` WHERE `id_order` = ' . (int)$id_order;
            $content = Db::getInstance()->getRow($sql, false);
            if ($content === false) {
                Db::getInstance()->insert('aramex_note', array(
                    'id_order' => (int)$id_order,
                    'note' => $note,
                ));
            } else {
                $note = $content['note'] . "\n" . $note;
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'aramex_note` 
                SET `note` = \'' . $note . '\' WHERE `id_order` = ' . (int)$id_order);
            }
        }
    }
}
