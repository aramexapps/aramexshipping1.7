<?php
/**
 * 2007-2017 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . 'aramexshipping/classes/core/DataAramexSettings.php');

/**
 * Main Controller Aramex plugin
 */
class Aramexshipping extends CarrierModule
{
    /**
     * @var boolean $config_form Config form
     */
    protected $config_form = false;
    /**
     * @var array $form_fields Form fields
     */
    private $form_fields;
    /**
     * @var string $id_carrier Id of carrier
     */
    public $id_carrier;

    /**
     * Aramex constructor.
     */
    public function __construct()
    {
        $this->form_fields = new DataAramexSettings();
        $this->name = 'aramexshipping';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Aramex team';
        $this->need_instance = 0;
        $this->module_key = 'a38dacd1929ad639d0e5bcde6d1f079d';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Aramex shipping module');
        $this->description = $this->l('Aramex shipping module');
    }

    /**
     * Installation function
     *
     * @return mixed
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }
        if (!extension_loaded('soap')) {
            $this->_errors[] = $this->l('Soap Client library was not installed');
            return false;
        }
        $this->addCarriers();
        Configuration::updateValue('ARAMEX_LIVE_MODE', false);

        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "aramex_note`(
	    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `id_order` INT(11) NOT NULL,
	    `note` VARCHAR(256) NOT NULL )";

        Db::getInstance()->Execute($sql);
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBeforeBodyClosingTag') &&
            $this->registerHook('updateCarrier') &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('displayAdminListBefore') &&
            $this->registerHook('footer') &&
            $this->registerHook('displayProductButtons') &&
            $this->registerHook('displayBackOfficeOrderActions');
        #reinstall module after adding hook!!!
    }

    /**
     * Installation function
     *
     * @return mixed
     */
    public function uninstall()
    {
        $this->removeCarriers();
        Configuration::deleteByName('ARAMEX_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     *
     * @return mixed
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitAramexModule')) == true) {
            $this->postProcess();
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     *
     * @return mixed
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAramexModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        //needed for multiselct fields
        if (Tools::isSubmit('submitAramexModule')) {
                Configuration::updateValue(
                    'ARAMEX_ALLOWED_DOMESTIC_METHODS',
                    serialize(Tools::getValue('ARAMEX_ALLOWED_DOMESTIC_METHODS'))
                );
                Configuration::updateValue(
                    'ARAMEX_ALLOWED_INTERNATIONAL_METHODS',
                    serialize(Tools::getValue('ARAMEX_ALLOWED_INTERNATIONAL_METHODS'))
                );
                Configuration::updateValue(
                    'ARAMEX_ALLOWED_DOMESTIC_ADDITIONAL_SERVICES',
                    serialize(Tools::getValue('ARAMEX_ALLOWED_DOMESTIC_ADDITIONAL_SERVICES'))
                );
                Configuration::updateValue(
                    'ARAMEX_ALLOWED_INTERNATIONAL_ADDITIONAL_SERVICES',
                    serialize(Tools::getValue('ARAMEX_ALLOWED_INTERNATIONAL_ADDITIONAL_SERVICES'))
                );
        }
        $configFormValues = $this->getConfigFormValues();
        $configFormValues['ARAMEX_ALLOWED_DOMESTIC_METHODS[]'] =
            Tools::unSerialize(Configuration::get('ARAMEX_ALLOWED_DOMESTIC_METHODS'));
        $configFormValues['ARAMEX_ALLOWED_INTERNATIONAL_METHODS[]'] =
            Tools::unSerialize(Configuration::get('ARAMEX_ALLOWED_INTERNATIONAL_METHODS'));
        $configFormValues['ARAMEX_ALLOWED_DOMESTIC_ADDITIONAL_SERVICES[]'] =
            Tools::unSerialize(Configuration::get('ARAMEX_ALLOWED_DOMESTIC_ADDITIONAL_SERVICES'));
        $configFormValues['ARAMEX_ALLOWED_INTERNATIONAL_ADDITIONAL_SERVICES[]'] =
            Tools::unSerialize(Configuration::get('ARAMEX_ALLOWED_INTERNATIONAL_ADDITIONAL_SERVICES'));

        $helper->tpl_vars = array(
            'fields_value' => $configFormValues, /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Gets configuration form
     *
     * @return array Fields
     */
    protected function getConfigForm()
    {
        $form_fields = $this->form_fields->getAllElements();
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Aramex Global Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'free',
                        'name' => 'FREE1',
                        'lang' => true,
                        'label' => $this->l('Client information'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'name' => 'ARAMEX_USER_NAME',
                        'label' => $this->l('Email'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_PASSWORD',
                        'label' => $this->l('Password'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_ACCOUNT_PIN',
                        'label' => $this->l('Account Pin'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_ACCOUNT_NUMBER',
                        'label' => $this->l('Account Number'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_ACCOUNT_ENTITY',
                        'label' => $this->l('Account Entity'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_ACCOUNT_COUNTRY_CODE',
                        'label' => $this->l('Account Country Code'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('COD Account'),
                        'name' => 'ARAMEX_ALLOWED_COD',
                        'is_bool' => true,
                        'desc' => $this->l('Optional account data'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_COD_ACCOUNT_NUMBER',
                        'label' => $this->l('COD Account Number'),
                        'desc' => $this->l('Optional account data'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_COD_ACCOUNT_PIN',
                        'label' => $this->l('COD Account Pin'),
                        'desc' => $this->l('Optional account data'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_COD_ACCOUNT_ENTITY',
                        'label' => $this->l('COD Account Entity'),
                        'desc' => $this->l('Optional account data'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_COD_ACCOUNT_COUNTRY_CODE',
                        'label' => $this->l('COD Account Country Code'),
                        'desc' => $this->l('Optional account data'),
                    ),
                    array(
                        'type' => 'free',
                        'name' => 'FREE2',
                        'lang' => true,
                        'label' => $this->l('Service Configuration'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Test Mode'),
                        'name' => 'ARAMEX_SANDBOX_FLAG',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_REPORT_ID',
                        'label' => $this->l('Report ID'),
                    ),
                    array(
                        'type' => 'select',
                        'multiple' => true,
                        'label' => $this->l('Allowed Domestic Methods'),
                        'name' => 'ARAMEX_ALLOWED_DOMESTIC_METHODS[]',
                        'options' => array(
                            'query' => $form_fields['allowed_domestic_methods'],
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'multiple' => true,
                        'label' => $this->l('Allowed Domestic Additional Services'),
                        'name' => 'ARAMEX_ALLOWED_DOMESTIC_ADDITIONAL_SERVICES[]',
                        'options' => array(
                            'query' => $form_fields['allowed_domestic_additional_services'],
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'multiple' => true,
                        'label' => $this->l('Allowed International Methods'),
                        'name' => 'ARAMEX_ALLOWED_INTERNATIONAL_METHODS[]',
                        'options' => array(
                            'query' => $form_fields['allowed_international_methods'],
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'multiple' => true,
                        'label' => $this->l('Allowed International Methods'),
                        'name' => 'ARAMEX_ALLOWED_INTERNATIONAL_ADDITIONAL_SERVICES[]',
                        'options' => array(
                            'query' => $form_fields['allowed_international_additional_services'],
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'free',
                        'name' => 'FREE3',
                        'lang' => true,
                        'label' => $this->l('Shipper Details'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_NAME',
                        'label' => $this->l('Name'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'name' => 'ARAMEX_EMAIL_ORIGIN',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_COMPANY',
                        'label' => $this->l('Company'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_ADDRESS',
                        'label' => $this->l('Address'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_COUNTRY',
                        'label' => $this->l('Country Code'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_CITY',
                        'label' => $this->l('City'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_POSTALCODE',
                        'label' => $this->l('Postal Code'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_STATE',
                        'label' => $this->l('State'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_PHONE',
                        'label' => $this->l('Phone'),
                    ),
                    array(
                        'type' => 'free',
                        'name' => 'FREE4',
                        'label' => $this->l('Shipment Email Template'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Shipment Email Copy Method'),
                        'name' => 'ARAMEX_COPY_METHOD',
                        'options' => array(
                            'query' => array(
                                array('key' => '0', 'name' => 'BBC'),
                                array('key' => '1', 'name' => 'Separate Email'),
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ARAMEX_COPY_TO',
                        'label' => $this->l('Shipment Email Copy To'),
                    ),
                    array(
                        'type' => 'free',
                        'name' => 'FREE5',
                        'lang' => true,
                        'label' => $this->l('Api Location Validator'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enabled'),
                        'name' => 'ARAMEX_APILOCATIONVALIDATOR_ACTIVE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'free',
                        'name' => 'FREE6',
                        'lang' => true,
                        'label' => $this->l('Api Ftront End Calculator'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enabled'),
                        'name' => 'ARAMEX_ARAMEXCALCULATOR_ACTIVE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     *
     * @return array Array of values
     */
    protected function getConfigFormValues()
    {

        return array(
            'ARAMEX_USER_NAME' => Configuration::get('ARAMEX_USER_NAME', true),
            'ARAMEX_PASSWORD' => Configuration::get('ARAMEX_PASSWORD', true),
            'ARAMEX_ACCOUNT_PIN' => Configuration::get('ARAMEX_ACCOUNT_PIN', true),
            'ARAMEX_ACCOUNT_NUMBER' => Configuration::get('ARAMEX_ACCOUNT_NUMBER', true),
            'ARAMEX_ACCOUNT_ENTITY' => Configuration::get('ARAMEX_ACCOUNT_ENTITY', true),
            'ARAMEX_ACCOUNT_COUNTRY_CODE' => Configuration::get('ARAMEX_ACCOUNT_COUNTRY_CODE', true),
            'ARAMEX_ALLOWED_COD' => Configuration::get('ARAMEX_ALLOWED_COD', true),
            'ARAMEX_COD_ACCOUNT_NUMBER' => Configuration::get('ARAMEX_COD_ACCOUNT_NUMBER', true),
            'ARAMEX_COD_ACCOUNT_PIN' => Configuration::get('ARAMEX_COD_ACCOUNT_PIN', true),
            'ARAMEX_COD_ACCOUNT_ENTITY' => Configuration::get('ARAMEX_COD_ACCOUNT_ENTITY', true),
            'ARAMEX_COD_ACCOUNT_COUNTRY_CODE' => Configuration::get('ARAMEX_COD_ACCOUNT_COUNTRY_CODE', true),
            'ARAMEX_SANDBOX_FLAG' => Configuration::get('ARAMEX_SANDBOX_FLAG', true),
            'ARAMEX_NAME' => Configuration::get('ARAMEX_NAME', true),
            'ARAMEX_EMAIL_ORIGIN' => Configuration::get('ARAMEX_EMAIL_ORIGIN', true),
            'ARAMEX_COMPANY' => Configuration::get('ARAMEX_COMPANY', true),
            'ARAMEX_ADDRESS' => Configuration::get('ARAMEX_ADDRESS', true),
            'ARAMEX_COUNTRY' => Configuration::get('ARAMEX_COUNTRY', true),
            'ARAMEX_CITY' => Configuration::get('ARAMEX_CITY', true),
            'ARAMEX_POSTALCODE' => Configuration::get('ARAMEX_POSTALCODE', true),
            'ARAMEX_STATE' => Configuration::get('ARAMEX_STATE', true),
            'ARAMEX_PHONE' => Configuration::get('ARAMEX_PHONE', true),
            'ARAMEX_COPY_TO' => Configuration::get('ARAMEX_COPY_TO', true),
            'ARAMEX_COPY_METHOD' => Configuration::get('ARAMEX_COPY_METHOD', true),
            'ARAMEX_APILOCATIONVALIDATOR_ACTIVE' => Configuration::get('ARAMEX_APILOCATIONVALIDATOR_ACTIVE', true),
            'ARAMEX_ARAMEXCALCULATOR_ACTIVE' => Configuration::get('ARAMEX_ARAMEXCALCULATOR_ACTIVE', true),
            'ARAMEX_REPORT_ID' => Configuration::get('ARAMEX_REPORT_ID', true),
            'FREE1' => Configuration::get('FREE1', true),
            'FREE2' => Configuration::get('FREE2', true),
            'FREE3' => Configuration::get('FREE3', true),
            'FREE4' => Configuration::get('FREE4', true),
            'FREE5' => Configuration::get('FREE5', true),
            'FREE6' => Configuration::get('FREE6', true),
        );
    }

    /**
     * Save form data.
     *
     * @return void
     */
    protected function postProcess()
    {

        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Gets Order Shipping Cost
     *
     * @param $params
     * @param $shipping_cost
     * @return array|bool|mixed
     */
    public function getOrderShippingCost($params, $shipping_cost)
    {
        if (Tools::getValue('controller') != 'payment' && Tools::getValue('controller') != 'order'
        && Tools::getValue('controller') != 'validation') {
            return false;
        }
        if (Context::getContext()->customer->logged == true) {
            $result = $this->calculateShipping($this->id_carrier);
            if ($result === false) {
                return $result;
            }
            //fix
            $result['params'] = $params;
            $result['shipping_cost'] = $shipping_cost;

            if ($result['type'] == "success") {
                return $result['value'];
            }
            if ($result['type'] == "error") {
                $carrier = new Carrier($this->id_carrier);
                if (isset($this->context->controller->errors[0]) && $this->context->controller->errors[0] ==
                    $result['error'] . " : " . $carrier->delay[1]) {
                    return false;
                }

                $this->context->controller->errors[] = $this->l($result['error'] . " : " . $carrier->delay[1]);
                return false;
            } else {
                return false;
            }
        }
    }

    /**
     * Gets shipping cost
     *
     * @param array $params parameters
     * @return mixed
     */
    public function getOrderShippingCostExternal($params)
    {
        return $params;
    }

    /**
     *
     */
    private function addCarriers()
    {
        $shipping_methods = $this->form_fields->getShippingMethods();

        foreach ($shipping_methods as $key => $shipping_method) {
            foreach ($shipping_method as $method) {
                if ($key == 'Domestic') {
                    $method = $method . ' (Domestic)';
                }

                if ($key == 'International') {
                    $method = $method . ' (International)';
                }

                $carrier = $this->addCarrier($method);
                $this->addZones($carrier);
                $this->addGroups($carrier);
                $this->addRanges($carrier);
            }
        }
    }

    /**
     * Adds carrier
     *
     * @param string $method Shipping method
     * @return bool|Carrier
     */
    protected function addCarrier($method)
    {
        $carrier = new Carrier();
        $carrier->name = $this->l($method);
        $carrier->is_module = 1;
        $carrier->active = 0;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 1;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;
        $carrier->max_weight = 250;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l($method);
        }
        if ($carrier->add() == true) {
            @copy(
                dirname(__FILE__) . '/views/img/carrier_image.jpg',
                _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg'
            );
            $carrier_var_name = $this->generateVariableName($method . ' id');
            Configuration::updateValue($carrier_var_name, (int)$carrier->id);
            return $carrier;
        }

        return false;
    }

    /**
     * Remove carriers
     *
     * @return bool
     */
    private function removeCarriers()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $shipping_methods = $this->form_fields->getShippingMethods();

        foreach ($shipping_methods as $key => $shipping_method) {
            foreach ($shipping_method as $method) {
                if ($key == 'Domestic') {
                    $method = $method . ' (Domestic)';
                }

                if ($key == 'International') {
                    $method = $method . ' (International)';
                }

                $carrier_var_name = $this->generateVariableName($method . ' id');
                $carrier = new Carrier(Configuration::get($carrier_var_name));
                if (Configuration::get('PS_CARRIER_DEFAULT') == (int)$carrier->id) {
                    $carriersD = Carrier::getCarriers(
                        $this->context->cookie->id_lang,
                        true,
                        false,
                        false,
                        null,
                        PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
                    );
                    foreach ($carriersD as $carrierD) {
                        if ($carrierD['active'] and !$carrierD['deleted'] and ($carrierD['name'] != $carrier->name)) {
                            Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
                        }
                    }
                }

                $carrier->deleted = 1;
                if (!$carrier->update()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Generates variable name
     * @param string $input String
     * @return mixed
     */
    private function generateVariableName($input)
    {
        $input = str_replace("(", "_", $input);
        $input = str_replace(")", "_", $input);
        $input = str_replace(":", "_", $input);
        $input = str_replace(".", "_", $input);
        return Tools::strtoupper($this->name . '_' . str_replace(" ", "_", $input));
    }

    /**
     * Adds groups
     * @param object $carrier Object of carrier
     * @return void
     */
    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    /**
     * Adds ranges
     *
     * @param object $carrier Object of carrier
     * @return void
     */
    protected function addRanges($carrier)
    {

        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '100000000';
        $range_price->add();

        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '250';
        $range_weight->add();
    }

    /**
     * Adds zones
     *
     * @param object $carrier Object of carrier
     * @return void
     */
    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     *
     * @return void
     */
    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addJS($this->_path . 'views/js/jquery.chained.js');
        $this->context->controller->addJS($this->_path . 'views/js/common.js');
        $this->context->controller->addJS($this->_path . 'views/js/jquery.validate.min.js');
        $this->context->controller->addCSS($this->_path . 'views/css/aramex.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     *
     * @return void
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * Hook update carrier
     *
     * @param $params
     */
    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
         */
    }

    /**
     * Calculates shipping costs
     *
     * @param string $carrier Name of carrier
     * @return array
     */
    private function calculateShipping($carrier)
    {
       
        $carrier = new Carrier($carrier);
        $pkgWeight = 0;
        $pkgQty = 0;
        $products = $this->context->cart->getProducts(true);
        foreach ($products as $product) {
            $pkgWeight = $pkgWeight + $product['quantity'] * $product['weight'];
            $pkgQty = $pkgQty + $product['quantity'];
        }

        $form_fields = $this->form_fields->getAllElements();
        $allowed_method = "";
        $id_address_delivery = Context::getContext()->cart->id_address_delivery;
        $address = new Address($id_address_delivery);
        $id_country = Country::getIdByName(null, $address->country);
        if (Configuration::get('ARAMEX_COUNTRY') == Country::getIsoById($id_country)) {
            $product_group = 'DOM';
            $allowed_methods_all = $form_fields['allowed_domestic_methods'];
            foreach ($allowed_methods_all as $allowed_method_internal) {
                if ($allowed_method_internal['name'] . ' (Domestic)' == $carrier->delay[1]) {
                    $allowed_method = $allowed_method_internal['id_option'];
                }
            }
        } else {
            $allowed_methods_all = $form_fields['allowed_international_methods'];
            $product_group = 'EXP';
            foreach ($allowed_methods_all as $allowed_method_internal) {
                if ($allowed_method_internal['name'] . ' (International)' == $carrier->delay[1]) {
                    $allowed_method = $allowed_method_internal['id_option'];
                }
            }
        }

        if ($allowed_method == "") {
            return false;
        }
        $info = $this->getInfo();

        $OriginAddress = array(
            'StateOrProvinceCode' => Configuration::get('ARAMEX_STATE'),
            'City' => Configuration::get('ARAMEX_CITY'),
            'PostCode' => Configuration::get('ARAMEX_POSTALCODE'),
            'CountryCode' => Configuration::get('ARAMEX_COUNTRY'),
        );

        $DestinationAddress = array(
            'StateOrProvinceCode' => $address->address1,
            'City' => $address->city,
            'PostCode' => $address->postcode,
            'CountryCode' => Country::getIsoById($address->id_country),
        );
        $ShipmentDetails = array(
            'PaymentType' => 'P',
            'ProductGroup' => $product_group,
            'ProductType' => $allowed_method,
            'ActualWeight' => array('Value' => $pkgWeight, 'Unit' => Configuration::get('PS_WEIGHT_UNIT')),
            'ChargeableWeight' => array('Value' => $pkgWeight, 'Unit' => Configuration::get('PS_WEIGHT_UNIT')),
            'NumberOfPieces' => $pkgQty
        );

        //SOAP object
        $soapClient = new SoapClient($info['baseUrl'] . 'aramex-rates-calculator-wsdl.wsdl');
        $currency = Currency::getCurrencyInstance((int)(Configuration::get('PS_CURRENCY_DEFAULT')));
        $baseCurrencyCode = $currency->iso_code;


        $params = array(
            'ClientInfo' => $info['clientInfo'],
            'OriginAddress' => $OriginAddress,
            'DestinationAddress' => $DestinationAddress,
            'ShipmentDetails' => $ShipmentDetails,
            'PreferredCurrencyCode' => $baseCurrencyCode
        );
        if ($allowed_method == "CDA") {
            $params['ShipmentDetails']['Services'] = "CODS";
        } else {
            $params['ShipmentDetails']['Services'] = "";
        }

        $t = date('d-m-Y');
        $secure_params = md5(
            Tools::jsonEncode($params) . date(
                "D",
                strtotime($t)
            ) . Tools::jsonEncode($currency) . Tools::jsonEncode($OriginAddress)
        );
        try {
            if (Cache::isStored('unique-id-for-aramex-module-and-request-result-' . $secure_params)) {
                return json_decode(
                    Cache::retrieve('unique-id-for-aramex-module-and-request-result-' . $secure_params),
                    true
                );
            }

            $results = $soapClient->CalculateRate($params);
            
            $response = array();
            if ($results->HasErrors) {
                if (count($results->Notifications->Notification) > 1) {
                    $error = "";
                    foreach ($results->Notifications->Notification as $notify_error) {
                        if ($notify_error->Code == "ERR20") {
                            return false;
                        }
                        $error .= 'Aramex: ' . $notify_error->Code . ' - ' . $notify_error->Message . "  *******  ";
                    }
                    $response['error'] = $error;
                } else {
                    if ($results->Notifications->Notification->Code == "ERR20") {
                        return false;
                    }
                    $response['error'] = 'Aramex: ' . $results->Notifications->Notification->Code . ' - ' .
                        $results->Notifications->Notification->Message;
                }
                $response['type'] = 'error';
            } else {
                $response['type'] = 'success';

                $local_currency = new Currency($this->context->cookie->id_currency);
                $full_price = $results->TotalAmount->Value * $local_currency->conversion_rate;
                Cache::store(
                    'unique-id-for-aramex-module-and-request-result-' . $secure_params,
                    Tools::jsonEncode(
                        array(
                            "type" => "success",
                            "value" => $full_price
                        )
                    )
                );
            }
        } catch (Exception $e) {
            $response['type'] = 'error';
            $response['error'] = $e->getMessage();
        }

        if ($response['type'] == 'error') {
            return $response;
        } else {
            return array(
                "type" => "success",
                "value" => $full_price
            );
        }
    }


    /**
     * Gets information about client
     *
     * @return array Information about client
     */
    private function getInfo()
    {
        $baseUrl = $this->getWsdlPath();
        $clientInfo = $this->getClientInfo();
        return (array('baseUrl' => $baseUrl, 'clientInfo' => $clientInfo));
    }

    /**
     * Gets path to Wsdl file
     *
     * @return string Path
     */
    private function getWsdlPath()
    {
        if (Configuration::get('ARAMEX_SANDBOX_FLAG') == 1) {
            $path = $this->getPath() . 'test/';
        } else {
            $path = $this->getPath();
        }
        return $path;
    }

    /**
     * Gets path to Wsdl file
     *
     * @return string Path
     */
    private function getPath()
    {
        return _PS_MODULE_DIR_ . 'aramexshipping/wsdl/';
    }

    /**
     * Gets clients info
     *
     * @return array Clients info
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
     * Hook to display apilocationvalidator.tpl file
     *
     * @param array $params Parameters
     * @return mixed
     */
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        if (Tools::getValue('controller') == 'order') {
            $allowed = Configuration::get('ARAMEX_APILOCATIONVALIDATOR_ACTIVE', true);
            $this->context->smarty->assign(
                array(
                    'allowed' => $allowed,
                    'dir_url' => $this->_path,
                )
            );
            return $this->display($this->_path, 'apilocationvalidator.tpl');
        }

        if (Tools::getValue('controller') == 'product') {
            $address = new Address((int)$params['cookie']->id_customer);
            $countryCollection = Country::getCountries(Context::getContext()->language->id);
            $reciverCountryCode = $address->id_country;
            $countries = array();
            foreach ($countryCollection as $key => $value) {
                $countries[Country::getNameById(Context::getContext()->language->id, $key)] = $value['id_country'];
            }
            ksort($countries);
            $currency = new CurrencyCore($this->context->cookie->id_currency);
            $currency->iso_code;
            $this->context->smarty->assign(
                array(
                    'aramexcalculator' => Configuration::get('ARAMEX_ARAMEXCALCULATOR_ACTIVE', true),
                    'product_id' => (int)Tools::getValue('id_product'),
                    'dir_url' => $this->_path,
                    'countries' => $countries,
                    "preloader" => $this->_path . '/views/img/preloader.gif',
                    "aramex_loader" => $this->_path . '/views/img/aramex_loader.gif',
                    "customer_city" => $address->city,
                    "customer_country" => $reciverCountryCode,
                    "customer_postcode"=> $address->postcode,
                    "currency1" => $currency->iso_code,
                )
            );
            return $this->display($this->_path, 'views/templates/front/aramexcalculator.tpl');
        }
    }

    /**
     * Hook to display views/templates/admin/shipment.tpl file
     * @param array $params Parameters
     * @return mixed
     */
    public function hookDisplayBackOfficeOrderActions($params)
    {
        $order = new Order($params["id_order"]);
        $products = $order->getProductsDetail();
        $totalWeight = $order->getTotalWeight();
        $id_customer = $order->id_customer;
        $customer = new Customer((int)$id_customer);
        $address = new Address($params['cart']->id_address_delivery);
        $carrier = new Carrier($params['cart']->id_carrier);


        $form_fields = $this->form_fields->getAllElements();
        $allowed_methods_all = $form_fields['allowed_international_methods'];
        $allowed_method = "";
        foreach ($allowed_methods_all as $allowed_method_internal) {
            if ($allowed_method_internal['name'] . ' (International)' == $carrier->delay[1]) {
                $allowed_method = $allowed_method_internal['id_option'];
            }
        }
        $id_address_delivery = Context::getContext()->cart->id_address_delivery;
        $address = new Address($id_address_delivery);
        $id_country = Country::getIdByName(null, $address->country);
        if (Configuration::get('ARAMEX_COUNTRY') == Country::getIsoById($id_country)) {
            $allowed_methods_all = $form_fields['allowed_domestic_methods'];
            foreach ($allowed_methods_all as $allowed_method_internal) {
                if ($allowed_method_internal['name'] . ' (Domestic)' == $carrier->delay[1]) {
                    $allowed_method = $allowed_method_internal['id_option'];
                }
            }
        }
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'aramex_note` WHERE `id_order` = ' . (int)$params["id_order"];
        $history = Db::getInstance()->getRow($sql, false);
        $history_list = $history['note'];
        $shipped = false;
        if (strpos($history_list, "- Order No") !== false) {
            $shipped = true;
        }
        $aramex_return_button = false;
        if (strpos($history_list, 'Return')) {
            $aramex_return_button = true;
        }
        $awbno = strstr($history_list, "- Order No", true);
        $awbno = trim($awbno, "AWB No.");
        if ($awbno != "") {
            $aramex_return_button = true;
        }

        $last_track = "";
        if (count($history_list)) {
            $pieces = array_reverse(explode("\n", $history_list));
            foreach ($pieces as $_history) {
                $awbno = strstr($_history, "- Order No", true);
                $awbno = trim($awbno, "AWB No.");

                if (isset($awbno)) {
                    if ((int)$awbno) {
                        $last_track = $awbno;
                        break;
                    }
                }
                $awbno = trim($awbno, "Aramex Shipment Return Order AWB No.");
                if (isset($awbno)) {
                    if ((int)$awbno) {
                        $last_track = $awbno;
                        break;
                    }
                }
            }
        }

        $form_fields = $this->form_fields->getAllElements();
        $allowed_domestic_methods_all = $form_fields['allowed_domestic_methods'];

        $allowed_domestic_methods = array();
        foreach ($allowed_domestic_methods_all as $allowed_method_internal) {
            foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_DOMESTIC_METHODS')) as $item) {
                if ($allowed_method_internal['id_option'] == $item) {
                    $allowed_domestic_methods[$allowed_method_internal['id_option']] = $allowed_method_internal['name'];
                }
            }
        }
        $allowed_international_methods_all = $form_fields['allowed_international_methods'];

        $allowed_international_methods = array();
        foreach ($allowed_international_methods_all as $allowed_method_internal) {
            foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_INTERNATIONAL_METHODS')) as $item) {
                if ($allowed_method_internal['id_option'] == $item) {
                    $allowed_international_methods[$allowed_method_internal['id_option']] =
                        $allowed_method_internal['name'];
                }
            }
        }

        $allowed_domestic_additional_services_all = $form_fields['allowed_domestic_additional_services'];
        $allowed_domestic_additional_services = array();
        foreach ($allowed_domestic_additional_services_all as $allowed_domestic_additional_services_internal) {
            foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_DOMESTIC_ADDITIONAL_SERVICES')) as $item) {
                if ($allowed_domestic_additional_services_internal['id_option'] == $item) {
                    $allowed_domestic_additional_services[$allowed_domestic_additional_services_internal['id_option']]
                        = $allowed_domestic_additional_services_internal['name'];
                }
            }
        }

        $allowed_international_additional_services_all = $form_fields['allowed_international_additional_services'];
        $allowed_international_additional_services = array();
        foreach ($allowed_international_additional_services_all as
                 $allowed_international_additional_services_internal) {
            foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_INTERNATIONAL_ADDITIONAL_SERVICES')) as $item) {
                if ($allowed_international_additional_services_internal['id_option'] == $item) {
                    $allowed_international_additional_services[
                        $allowed_international_additional_services_internal['id_option']
                    ] = $allowed_international_additional_services_internal['name'];
                }
            }
        }

        $session = false;
        if (isset($_SESSION['form_data'])) {
            $session = true;
        }
        $countryCollection = Country::getCountries(Context::getContext()->language->id);
        $reciverCountryCode = Country::getIsoById($address->id_country);
        $countries = array();
        foreach ($countryCollection as $key => $value) {
            $countries[Country::getNameById(Context::getContext()->language->id, $key)] = $value['iso_code'];
        }
        ksort($countries);
        $currency = Currency::getCurrencyInstance((int)$order->id_currency);
        $currencyCode = $currency->iso_code;


        $this->context->smarty->assign(
            array(
                'order_id' => $params["id_order"],
                'dir_url' => $this->_path,
                'aramex_return_button' => $aramex_return_button,
                'shipped' => $shipped,
                'totalWeight' => $totalWeight,
                'account' => Configuration::get('ARAMEX_ACCOUNT_NUMBER'),
                'pin' => Configuration::get('ARAMEX_ACCOUNT_PIN'),
                'name' => Configuration::get('ARAMEX_NAME'),
                'email' => Configuration::get('ARAMEX_EMAIL_ORIGIN'),
                'company' => Configuration::get('ARAMEX_COMPANY'),
                'address' => Configuration::get('ARAMEX_ADDRESS'),
                'country' => Configuration::get('ARAMEX_COUNTRY'),
                'city' => Configuration::get('ARAMEX_CITY'),
                'postalcode' => Configuration::get('ARAMEX_POSTALCODE'),
                'state' => Configuration::get('ARAMEX_STATE'),
                'phone' => Configuration::get('ARAMEX_PHONE'),
                'allowed_domestic_methods' => $allowed_domestic_methods,
                'allowed_international_methods' => $allowed_international_methods,
                'allowed_domestic_additional_services' => $allowed_domestic_additional_services,
                'allowed_international_additional_services' => $allowed_international_additional_services,
                'allowed_cod' => Configuration::get('ARAMEX_ALLOWED_COD'),
                'unit' => Configuration::get('PS_WEIGHT_UNIT'),
                'allowed_method' => $allowed_method,
                'products' => $products,
                'session' => $session,
                'customer' => $customer,
                'address_reciver' => $address->address1,
                'address_reciver2' => $address->address2,
                'reciverCountryCode' => $reciverCountryCode,
                'reciverCity' => $address->city,
                'reciverPostcode' => $address->postcode,
                'reciverPhone' => $address->phone,
                'countries' => $countries,
                'total_paid' => round($order->total_paid, 2),
                'currencyCode' => $currencyCode,
                'allowed' => Configuration::get('ARAMEX_APILOCATIONVALIDATOR_ACTIVE'),
                'currentTimeH' => date("H", time()),
                'currentTimei' => date("i", time()),
                'currentTimeM' => date("m/d/Y", time()),
                'M' => date("m", time()),
                'D' => date("d", time()),
                'Y' => date("Y", time()),
                'timePlusOne' => date("H", time()) + 1,
                "last_track" => $last_track,
                "note" => array_reverse(explode("\n", $history_list)),
                "preloader" => $this->_path . '/views/img/preloader.gif'
            )
        );
        return $this->display($this->_path, 'views/templates/admin/shipment.tpl');
    }

    /**
     * Unsets Session
     * @param array $params Session data
     */
    public function hookDisplayAdminOrder($params)
    {
        unset($_SESSION['form_data']);
        unset($_SESSION['aramex_errors']);
        unset($_SESSION['aramex_errors_printlabel']);
    }

    /**
     * Hook to display views/templates/admin/bulk.tpl file
     * @param array $params Parameters
     * @return mixed
     */
    public function hookDisplayAdminListBefore($params)
    {
        if (Tools::getValue('controller') == 'AdminOrders') {
            $form_fields = $this->form_fields->getAllElements();
            $allowed_domestic_methods_all = $form_fields['allowed_domestic_methods'];

            $allowed_domestic_methods = array();
            foreach ($allowed_domestic_methods_all as $allowed_method_internal) {
                foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_DOMESTIC_METHODS')) as $item) {
                    if ($allowed_method_internal['id_option'] == $item) {
                        $allowed_domestic_methods[$allowed_method_internal['id_option']] =
                            $allowed_method_internal['name'];
                    }
                }
            }
            $allowed_international_methods_all = $form_fields['allowed_international_methods'];

            $allowed_international_methods = array();
            foreach ($allowed_international_methods_all as $allowed_method_internal) {
                foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_INTERNATIONAL_METHODS')) as $item) {
                    if ($allowed_method_internal['id_option'] == $item) {
                        $allowed_international_methods[$allowed_method_internal['id_option']] =
                            $allowed_method_internal['name'];
                    }
                }
            }

            $allowed_domestic_additional_services_all = $form_fields['allowed_domestic_additional_services'];
            $allowed_domestic_additional_services = array();
            foreach ($allowed_domestic_additional_services_all as $allowed_domestic_additional_services_internal) {
                foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_DOMESTIC_ADDITIONAL_SERVICES')) as $item) {
                    if ($allowed_domestic_additional_services_internal['id_option'] == $item) {
                        $allowed_domestic_additional_services[
                            $allowed_domestic_additional_services_internal['id_option']
                        ] = $allowed_domestic_additional_services_internal['name'];
                    }
                }
            }

            $allowed_international_additional_services_all = $form_fields['allowed_international_additional_services'];
            $allowed_international_additional_services = array();
            foreach ($allowed_international_additional_services_all as
                     $allowed_international_additional_services_internal) {
                foreach (unserialize(Configuration::get('ARAMEX_ALLOWED_INTERNATIONAL_ADDITIONAL_SERVICES')) as
                         $item) {
                    if ($allowed_international_additional_services_internal['id_option'] == $item) {
                        $allowed_international_additional_services[
                            $allowed_international_additional_services_internal['id_option']
                        ] = $allowed_international_additional_services_internal['name'];
                    }
                }
            }
            $currency = Context::getContext()->currency;
            $currencyCode = $currency->iso_code;
            $this->context->smarty->assign(
                array(
                    'currencyCode' => $currencyCode,
                    'dir_url' => $this->_path,
                    'allowed_domestic_methods' => $allowed_domestic_methods,
                    'allowed_international_methods' => $allowed_international_methods,
                    'allowed_domestic_additional_services' => $allowed_domestic_additional_services,
                    'allowed_international_additional_services' => $allowed_international_additional_services,
                    "preloader" => $this->_path . '/views/img/preloader.gif'

                )
            );
            return $this->display($this->_path, 'views/templates/admin/bulk.tpl');
        }
    }

    /**
     * Hook to display aramexcalculator_button.tpl
     * @param array $params Parameters
     * @return mixed
     */
    public function hookDisplayProductButtons($params)
    {
        $this->context->smarty->assign(
            array(
                'aramexcalculator' => Configuration::get('ARAMEX_ARAMEXCALCULATOR_ACTIVE', true)
            )
        );
        return $this->display($this->_path, 'views/templates/front/aramexcalculator_button.tpl');
    }
}
