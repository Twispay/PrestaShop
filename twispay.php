<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Twispay extends PaymentModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'twispay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Active Design';
        $this->need_instance = 0;
        $this->module_key = 'd89110977c71a97d064d510cc90d760c';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Credit card payments by Twispay');
        $this->description = $this->l('Module for Twispay payment gateway. Your customers can now pay with credit card.');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('TWISPAY_LIVE_MODE', false);

        return parent::install() &&
            $this->createTransactionsTable() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayPaymentReturn') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('displayAdminOrderLeft');
    }

    public function uninstall()
    {
        Configuration::deleteByName('TWISPAY_LIVE_MODE');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'twispay_transactions`');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $messages = "";
        if (((bool)Tools::isSubmit('submitTwispayModule')) == true) {
            $post = $this->postProcess();
            if ($post === true) {
                $messages = $this->displayConfirmation($this->l('Settings have been saved.'));
            } elseif ($post === false) {
                $messages = $this->displayError($this->l('There was an error'));
            } else {
                $messages = $this->displayError($post);
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        
        $output = $this->renderTransactionsList();

        return $messages.$this->renderForm().$output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
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
        $helper->submit_action = 'submitTwispayModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Twispay settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'TWISPAY_LIVE_MODE',
                        'is_bool' => false,
                        'desc' => $this->l('Select "YES" if you wish to use the payment gateway in Production or "No" if you want to use it in staging mode.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-lock"></i>',
                        'desc' => $this->l('Enter the SITE ID for staging mode'),
                        'name' => 'TWISPAY_SITEID_STAGING',
                        'label' => $this->l('Staging Site ID'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter the Private key for staging mode'),
                        'name' => 'TWISPAY_PRIVATEKEY_STAGING',
                        'label' => $this->l('Staging Private key'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-lock"></i>',
                        'desc' => $this->l('Enter the SITE ID for live mode'),
                        'name' => 'TWISPAY_SITEID_LIVE',
                        'label' => $this->l('Live Site ID'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter the Private key for live mode'),
                        'name' => 'TWISPAY_PRIVATEKEY_LIVE',
                        'label' => $this->l('Live Private key'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Put this URL in your twispay account'),
                        'name' => 'TWISPAY_NOTIFICATION_URL',
                        'label' => $this->l('Server-to-server notification URL'),
                        'readonly' => true,
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
     */
    protected function getConfigFormValues()
    {
        return array(
            'TWISPAY_LIVE_MODE' => Configuration::get('TWISPAY_LIVE_MODE'),
            'TWISPAY_SITEID_STAGING' => Configuration::get('TWISPAY_SITEID_STAGING'),
            'TWISPAY_PRIVATEKEY_STAGING' => Configuration::get('TWISPAY_PRIVATEKEY_STAGING'),
            'TWISPAY_SITEID_LIVE' => Configuration::get('TWISPAY_SITEID_LIVE'),
            'TWISPAY_PRIVATEKEY_LIVE' => Configuration::get('TWISPAY_PRIVATEKEY_LIVE'),
            'TWISPAY_NOTIFICATION_URL' => $this->context->link->getModuleLink('twispay', 'validation'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $success = true;
        
        foreach (array_keys($form_values) as $key) {
            if (!Configuration::updateValue($key, Tools::getValue($key))) {
                $success = false;
            }
        }
        return $success;
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name || Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            Media::addJsDef(array(
                'TWISPAY_LIVE_MODE' => Configuration::get('TWISPAY_LIVE_MODE'),
            ));
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }
    
    public function hookPaymentOptions($params)
    {
        if (!$this->active || !self::getKeysInfo()) {
            return;
        }

        $this->smarty->assign(
            $this->getPaymentVars($params)
        );
        
        $this->smarty->assign(array(
            'logos_folder' => _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/'.$this->name.'/views/img/',
        ));
        
        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
                ->setCallToActionText($this->trans('Pay by credit or debit card', array(), 'Modules.Twispay.Shop'))
                ->setForm($this->fetch('module:twispay/views/templates/hook/twispay_payment_form.tpl'))
                ->setAdditionalInformation($this->fetch('module:twispay/views/templates/hook/twispay_payment_extra.tpl'));
        $payment_options = array(
            $newOption,
        );

        return $payment_options;
    }
    
    public function hookDisplayAdminOrderLeft($params)
    {
        $id_order = (int)$params['id_order'];
        $data = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'twispay_transactions` 
        WHERE `id_cart` = (SELECT `id_cart` FROM `'._DB_PREFIX_.'orders` WHERE `id_order` = "'.$id_order.'")');
        if (!$data) {
            return false;
        } else {
            return $this->buildOrderMessage($data);
        }
    }
    
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path.'views/css/front.css');
    }

    public function hookDisplayPaymentReturn($params)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>')) {
            return false;
        }
        if ($this->active == false) {
            return;
        }
        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }
        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }
    
    /* Method for sorting an array recursive ** Needed for data encoding ** */
    
    public static function recursiveKeySort(array &$data)
    {
        ksort($data, SORT_STRING);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::recursiveKeySort($data[$key]);
            }
        }
    }
    
    /* Method for gettings keys info (siteId and privateKey) */
    
    public static function getKeysInfo()
    {
        if (Configuration::get('TWISPAY_LIVE_MODE')) {
            $info = array(
                'privateKey' => Configuration::get('TWISPAY_PRIVATEKEY_LIVE'),
                'siteId' => Configuration::get('TWISPAY_SITEID_LIVE'),
                'formUrl' => 'https://secure.twispay.com',
            );
        } else {
            $info = array(
                'privateKey' => Configuration::get('TWISPAY_PRIVATEKEY_STAGING'),
                'siteId' => Configuration::get('TWISPAY_SITEID_STAGING'),
                'formUrl' => 'https://secure-stage.twispay.com',
            );
        }
        if (!$info['privateKey'] || !$info['siteId']) {
            return false;
        }
        return $info;
    }
    
    /* Method for adding siteId, apiKey and checksum to the data arraay */
    
    public static function buildDataArray($data)
    {
        $keys = self::getKeysInfo();
        if (!$keys) {
            return false;
        }
        $apiKey = $keys['privateKey'];
        $siteId = $keys['siteId'];
        $data['siteId'] = $siteId;
        
        unset($data['checksum']);
        self::recursiveKeySort($data);
        $query = http_build_query($data);
        
        $encoded = hash_hmac('sha512', $query, $apiKey, true);
        $data['checksum'] = base64_encode($encoded);
        
        return $data;
    }
    
    /* Method for decrypting data received by Twispay */
    
    public static function twispayDecrypt($encrypted)
    {
        $keys = self::getKeysInfo();
        if (!$keys) {
            return false;
        }
        $apiKey = $keys['privateKey'];
        
        $encrypted = (string)$encrypted;
        if (!Tools::strlen($encrypted)) {
            return null;
        }
        if (strpos($encrypted, ',') !== false) {
            $encryptedParts = explode(',', $encrypted, 2);
            $iv = base64_decode($encryptedParts[0]);
            if (false === $iv) {
                throw new Exception("Invalid encryption iv");
            }
            $encrypted = base64_decode($encryptedParts[1]);
            if (false === $encrypted) {
                throw new Exception("Invalid encrypted data");
            }
            $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $apiKey, OPENSSL_RAW_DATA, $iv);
            if (false === $decrypted) {
                throw new Exception("Data could not be decrypted");
            }
            return $decrypted;
        }
        return null;
    }
    
    public function getPaymentVars($params = false)
    {
        $inputs = array();
        if (!$params) {
            $params = array();
            $params['cookie'] = $this->context->cookie;
            $params['cart'] = $this->context->cart;
        }
        
        /* Customer data */
        
        $inputs['identifier'] = '_'.$params['cart']->id_customer;
        $customerObj = new Customer((int)$params['cart']->id_customer);
        if (Validate::isLoadedObject($customerObj)) {
            $inputs['firstName'] = $customerObj->firstname;
            $inputs['lastName'] = $customerObj->lastname;
            $inputs['customerTags'] = array(0 => "");
            $inputs['invoiceEmail'] = "";
        }
        
        /* Customer address data */
        $id_address = (int)$params['cart']->id_address_invoice;
        if ($id_address) {
            $addressObj = new Address($id_address);
            if (Validate::isLoadedObject($addressObj)) {
                $countryObj = new Country($addressObj->id_country);
                if (Validate::isLoadedObject($countryObj)) {
                    $inputs['country'] = $countryObj->iso_code;
                    if ((int)$addressObj->id_state && $inputs['country'] == 'US') {
                        $state = new State($addressObj->id_state);
                        if (Validate::isLoadedObject($state)) {
                            $inputs['state'] = $state->iso_code;
                        }
                    }
                }
                $inputs['city'] = $addressObj->city;
                $inputs['zipCode'] = $addressObj->postcode;
                $inputs['address'] = $addressObj->address1;
                if ($addressObj->address2) {
                    $inputs['address'] .= " ".$addressObj->address2;
                }
                if ($addressObj->phone_mobile) {
                    $inputs['phone'] = $addressObj->phone_mobile;
                } elseif ($addressObj->phone) {
                    $inputs['phone'] = $addressObj->phone;
                }
                $inputs['email'] = $customerObj->email;
            }
        }
        
        /* Transaction details data */
        $cart = $params['cart'];
        $inputs['cardTransactionMode'] = $this->getCardTransactionMode($cart);
        $inputs['amount'] = (float)number_format((float)$cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
        $currency = new Currency((int)$cart->id_currency);
        $inputs['currency'] = $currency->iso_code;
        $inputs['orderType'] = $this->getOrderType($params);
        $inputs['orderId'] = self::buildOrderId($cart->id);
        $inputs['cardId'] = $this->getPreviousCardId($params);
        $inputs['description'] = $this->getCartDescription($cart);
        $inputs['backUrl'] = $this->getBackUrl($cart);
        $inputs['orderTags'] = array(0 => "");
        
        /* Order details data */
        $products = $cart->getProducts();
        $products_i = 0;
        foreach ($products as $product) {
            $inputs['item'][$products_i] = $product['name'];
            $inputs['unitPrice'][$products_i] = number_format((float)$product['price_wt'], 2);
            $inputs['units'][$products_i] = (float)$product['cart_quantity'];
            $inputs['subTotal'][$products_i] = number_format(number_format((float)$inputs['unitPrice'][$products_i], 2)
                * number_format((float)$inputs['units'][$products_i], 2), 2);
            if (!empty($product['attributes_small'])) {
                $inputs['item'][$products_i] .= " - ".$product['attributes_small'];
            } elseif (!empty($product['attributes'])) {
                $inputs['item'][$products_i] .= " - ".$product['attributes'];
            }
            
            $products_i++;
        }
        $shipping = (float)number_format((float)$cart->getOrderTotal(true, Cart::ONLY_SHIPPING), 2);
        if ($shipping > 0) {
            $inputs['item'][$products_i] = $this->l('Shipping');
            $inputs['unitPrice'][$products_i] = $shipping;
            $inputs['units'][$products_i] = 1;
            $inputs['subTotal'][$products_i] = $shipping;
        }
        $discounts = (float)number_format((float)$cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS), 2);
        if ($discounts > 0) {
            $inputs['item'][$products_i] = $this->l('Discounts');
            $inputs['unitPrice'][$products_i] = -$discounts;
            $inputs['units'][$products_i] = 1;
            $inputs['subTotal'][$products_i] = -$discounts;
        }
        
        $inputs = self::buildDataArray($inputs);
        
        $data = array();
        
        $data['inputs'] = $inputs;
        $data['action'] = self::getPaymentFormActionUrl();
        
        return $data;
    }
    
    public function getOrderType($params)
    {
        return 'purchase';
    }
    
    public function getPreviousCardId($params)
    {
        return 0;
    }
    
    public static function getPaymentFormActionUrl()
    {
        $keys = self::getKeysInfo();
        if ($keys) {
            return $keys['formUrl'];
        }
        return false;
    }
    
    public function getCardTransactionMode($cart)
    {
        return "authAndCapture";
    }
    
    public function getCartDescription($cart)
    {
        return "";
    }
    
    public function getBackUrl($cart)
    {
        $id_customer = (int)$cart->id_customer;
        $secure_key = "";
        if ($id_customer) {
            $customer = new Customer($id_customer);
            if (Validate::isLoadedObject($customer)) {
                $secure_key = $customer->secure_key;
            }
        }
        return $this->context->link->getModuleLink(
            'twispay',
            'confirmation',
            array('cart_id' => $cart->id, 'secure_key' => $secure_key)
        );
    }
    
    public function log($string = false)
    {
        $log_file = dirname(__FILE__).'/twispay_log.txt';
        if (!$string) {
            $string = PHP_EOL.PHP_EOL;
        } else {
            $string = "[".date('Y-m-d H:i:s')."] ".$string;
        }
        @file_put_contents($log_file, $string.PHP_EOL, FILE_APPEND);
    }
    
    public static function getResultStatuses()
    {
        return array("complete-ok");
    }
    
    public function renderTransactionsList()
    {
        $this->fields_list = array(
            'id_transaction' => array(
                'title' => $this->l('ID'),
                'type' => 'text',
                'search' => false,
            ),
            'order_reference' => array(
                'title' => $this->l('Order reference'),
                'type' => 'text',
                'search' => false,
            ),
            'customer_name' => array(
                'title' => $this->l('Customer name'),
                'type' => 'text',
                'search' => false,
            ),
            'transactionId' => array(
                'title' => $this->l('Transaction ID'),
                'type' => 'text',
                'search' => false,
            ),
            'transactionKind' => array(
                'title' => $this->l('Transaction Kind'),
                'type' => 'text',
                'search' => false,
            ),
            'amount_formatted' => array(
                'title' => $this->l('Amount'),
                'type' => 'text',
                'search' => false,
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'type' => 'text',
                'search' => false,
            ),
            'date' => array(
                'title' => $this->l('Date'),
                'type' => 'text',
                'search' => false,
            ),
        );
        $helper = new HelperList();
         
        $helper->shopLinkType = '';
         
        $helper->simple_header = true;
         
        // Actions to be displayed in the "Actions" column
         
        $helper->identifier = 'id_transaction';
        $helper->show_toolbar = true;
        $helper->title = 'Transactions list';
        $helper->table = 'twispay_transactions';
        $helper->listTotal = $this->getTransactionsNumber();
        $helper->_default_pagination = 20;
        $helper->simple_header = false;
        $page = (int)Tools::getValue('submitFilter'.$helper->table);
        $selected_pagination = Tools::getValue(
            $helper->table.'_pagination',
            isset($this->context->cookie->{$helper->table.'_pagination'}) ? $this->context->cookie->{$helper->table.
            '_pagination'} : $helper->_default_pagination
        );
         
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        return $helper->generateList($this->getTransactions($page, $selected_pagination), $this->fields_list);
    }
    
    public function logTransaction($data)
    {
        $columns = array(
            'status',
            'id_cart',
            'identifier',
            'customerId',
            'orderId',
            'cardId',
            'transactionId',
            'transactionKind',
            'amount',
            'currency',
            'timestamp',
        );
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $columns)) {
                unset($data[$key]);
            } else {
                $data[$key] = pSQL($data[$key]);
            }
        }
        if (!empty($data['timestamp'])) {
            $data['date'] = pSQL(date('Y-m-d H:i:s', $data['timestamp']));
            unset($data['timestamp']);
        }
        if (!empty($data['identifier'])) {
            $data['identifier'] = (int)str_replace('_', '', $data['identifier']);
        }
        Db::getInstance()->insert('twispay_transactions', $data);
    }
    
    public function createTransactionsTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."twispay_transactions` (
            `id_transaction` int(11) NOT NULL AUTO_INCREMENT,
            `status` varchar(16) NOT NULL,
            `id_cart` int(11) NOT NULL,
            `identifier` int(11) NOT NULL,
            `customerId` int(11) NOT NULL,
            `orderId` int(11) NOT NULL,
            `cardId` int(11) NOT NULL,
            `transactionId` int(11) NOT NULL,
            `transactionKind` varchar(16) NOT NULL,
            `amount` float NOT NULL,
            `currency` varchar(8) NOT NULL,
            `date` DATETIME NOT NULL,
            PRIMARY KEY (`id_transaction`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
        return Db::getInstance()->execute($sql);
    }
    
    public function getTransactions($page, $selected_pagination)
    {
        if ((int)$page <= 0) {
            $page = 1;
        }
        $limit = ((int)$page-1)*$selected_pagination;
        return Db::getInstance()->executeS('SELECT tt.*, o.`reference` 
        as `order_reference`, CONCAT(tt.`amount`, " ", tt.`currency`) 
        as `amount_formatted`, CONCAT(c.`firstname`," ", c.`lastname`) 
        as `customer_name`  FROM `'._DB_PREFIX_.'twispay_transactions` tt 
        LEFT JOIN `'._DB_PREFIX_.'orders` o LEFT JOIN `'._DB_PREFIX_.'customer` c 
        ON (c.`id_customer` = o.`id_customer`) ON (o.`id_cart` = tt.`id_cart`) 
        ORDER BY `id_transaction` DESC LIMIT '. (int)$limit .', '.(int)$selected_pagination);
    }
    
    public function getTransactionsNumber()
    {
        return (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'twispay_transactions`');
    }
    
    public function buildOrderMessage($data)
    {
        $this->context->smarty->assign('data', $data);
        return $this->display(__FILE__, 'views/templates/admin/payment_message.tpl');
    }
    
    public function getPath()
    {
        return dirname(__FILE__);
    }
    
    public function checkValidation($decrypted, $usingOpenssl = true)
    {
        $json = Tools::jsonDecode($decrypted);
        if (!$json) {
            return false;
        }
        $this->log('[RESPONSE] decrypted string ('.(int)$usingOpenssl.'): '.$decrypted);
        /* Validating the fields */
        if (empty($json->externalOrderId)) {
            $this->_errors[] = $this->l('Empty externalOrderId');
        } else {
            $order_id = Order::getOrderByCartId(self::getOrderIdFromString($json->externalOrderId));
            if ($order_id) {
                $this->log(sprintf($this->l('[RESPONSE-ERROR] Order already validated, order id %s'), $order_id));
                $this->log();
                return true;
            }
        }
        $id_cart = (!empty($json->externalOrderId)) ? self::getOrderIdFromString($json->externalOrderId) : 0;
        $cart = new Cart($id_cart);
        $cartFound = false;
        if (Validate::isLoadedObject($cart)) {
            $cartFound = true;
        }
        if (empty($json->status) && empty($json->transactionStatus)) {
            $this->_errors[] = $this->l('Empty status');
        }
        if (empty($json->amount)) {
            if ($cartFound) {
                $json->amount = (float)number_format((float)$cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
            } else {
                $this->_errors[] = $this->l('Empty amount');
            }
        }
        if (empty($json->currency)) {
            if ($cartFound) {
                $currency = new Currency($cart->id_currency);
                if (Validate::isLoadedObject($currency)) {
                    $json->currency = $currency->iso_code;
                } else {
                    $this->_errors[] = $this->l('Empty currency');
                }
            } else {
                $this->_errors[] = $this->l('Empty currency');
            }
        }
        if (empty($json->identifier)) {
            $this->_errors[] = $this->l('Empty identifier');
        }
        if (empty($json->orderId)) {
            $this->_errors[] = $this->l('Empty orderId');
        }
        if (empty($json->transactionId)) {
            $this->_errors[] = $this->l('Empty transactionId');
        }
        /*if(empty($json->transactionKind) && empty($json->transactionMethod)) {
            $this->_errors[] = $this->l('Empty transactionKind');
        }*/
        if (sizeof($this->_errors)) {
            foreach ($this->_errors as $err) {
                $this->log('[RESPONSE-ERROR] '.$err);
            }
            $this->log();
            return false;
        } else {
            $data = array(
                'id_cart' => $id_cart,
                'status' => (empty($json->status)) ? $json->transactionStatus : $json->status,
                'amount' => (float)$json->amount,
                'currency' => $json->currency,
                'identifier' => $json->identifier,
                'orderId' => (int)$json->orderId,
                'transactionId' => (empty($json->transactionId)) ? 0 : (int)$json->transactionId,
                'customerId' => (int)$json->customerId,
                'transactionKind' => self::getTransactionKind($json),
                'cardId' => (!empty($json->cardId)) ? (int)$json->cardId : 0,
                'timestamp' => (is_object($json->timestamp)) ? time() : $json->timestamp,
            );
            $this->log('[RESPONSE] Data: '.Tools::jsonEncode($data));
            
            $id_cart = $data['id_cart'];
            $cart = new Cart($id_cart);
            if (!Validate::isLoadedObject($cart)) {
                $this->log(sprintf($this->l('[RESPONSE-ERROR] Cart #%s could not be loaded'), $id_cart));
                $this->log();
                return false;
            }
            $id_customer = (int)$cart->id_customer;
            $customer = new Customer($id_customer);
            if (!Validate::isLoadedObject($customer)) {
                $this->log(sprintf($this->l('[RESPONSE-ERROR] Customer #%s could not be loaded.'), $id_customer));
                $this->log();
                return false;
            }
            $secure_key = $customer->secure_key;
            
            $id_currency = (int)Currency::getIdByIsoCode($data['currency']);
            if (!$id_currency) {
                $this->log(sprintf($this->l('[RESPONSE-ERROR] Wrong Currency: %s'), $data['currency']));
                $this->log();
                return false;
            }
            if (!in_array($data['status'], $this->getResultStatuses())) {
                $this->log(sprintf($this->l('[RESPONSE-ERROR] Wrong status (%s)'), $data['status']));
                $this->log();
                $this->logTransaction($data);
                return false;
            }
            $this->log('[RESPONSE] Status complete-ok');
            
            $validated = $this->validateOrder(
                $data['id_cart'],
                Configuration::get('PS_OS_PAYMENT'),
                $data['amount'],
                $this->displayName,
                null,
                null,
                $id_currency,
                false,
                $secure_key
            );
            if ($validated) {
                $this->logTransaction($data);
                $this->log(sprintf($this->l('[RESPONSE] Validating order: %s'), Tools::jsonEncode($validated)));
                $this->log();
            } else {
                $this->log(
                    sprintf($this->l('[RESPONSE-ERROR] Could not validate order: %s'), Tools::jsonEncode($validated))
                );
                $this->log();
            }
            return $validated;
        }
    }
    
    public static function buildOrderId($id_cart)
    {
        return $id_cart.'_'.time();
    }
    
    public static function getOrderIdFromString($string)
    {
        if (strpos($string, '_') !== -1) {
            $array = explode('_', $string);
            return (int)$array[0];
        } else {
            return (int)$string;
        }
    }
    
    public static function getTransactionKind($json)
    {
        $kind = 'n/a';
        if (!empty($json->transactionKind)) {
            $kind = $json->transactionKind;
        }
        if (!empty($json->transactionMethod)) {
            $kind = $json->transactionMethod;
        }
        return $kind;
    }
}
