<?php

require_once _PS_ROOT_DIR_.'/modules/descomsms/libs/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

define('VERSION', '1.0.2');
define('LATEST_VERSION_URL', 'https://www.descomsms.com/download/prestashop/latest.php');

class descomsms extends Module
{
    public function __construct()
    {
        $this->name = 'descomsms';
        $this->tab = 'emailing';
        $this->version = VERSION;
        $this->author = 'descomSMS.com';
        $this->need_instance = 0;
        $this->is_configurable = 0;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->versionURL = LATEST_VERSION_URL;

        parent::__construct();

        $this->displayName = $this->l('DescomSMS - SMS and let them know');
        $this->description = $this->l('Turn on SMS notifications to help your customers feeling confident on your site. Send them SMS notification for payment confirmation, order delivery and if you are running out of stock,  keep your customers updated on the availability of their wishlist.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->db = Db::getInstance();
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('actionOrderStatusPostUpdate') ||
            !$this->registerHook('actionUpdateQuantity') ||
        !$this->registerHook('displayBackOfficeHeader')
        ) {
            return false;
        } else {
            $this->CreateTab('AdminDescomsms', 'DescomSMS', 0);
            //$idParent = Tab::getIdFromClassName("SMSpubli");
            //$this->CreateTab("Home", $this->l('Home'), $idParent);

            $this->InitConfigValues();
        }
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !Configuration::deleteByName('MYMODULE_NAME')) {
            return false;
        } else {
            $this->DeleteTab('AdminDescomsms');
        }

        return true;
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $key = strval(Tools::getValue('DESCOMSMS_KEY'));
            $user = strval(Tools::getValue('DESCOMSMS_USER'));
            $pass = strval(Tools::getValue('DESCOMSMS_PASS'));

            if (!$key || empty($key)) {
                $key = base64_encode(openssl_random_pseudo_bytes(32));
                Configuration::updateValue('DESCOMSMS_KEY', $key);
            }

            if (!$user || empty($user) || !Validate::isGenericName($user) ||
            !$pass || empty($pass) || !Validate::isGenericName($pass)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('DESCOMSMS_USER', $user);
                Configuration::updateValue('DESCOMSMS_PASS', $this->MyEncrypt($pass, $key));
                $this->SetHookModulePosition();

                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('DescomSMS user'),
                    'name'     => 'DESCOMSMS_USER',
                    'class'    => 'lg',
                    'required' => true,
                ],
                [
                    'type'     => 'password',
                    'label'    => $this->l('DescomSMS password'),
                    'name'     => 'DESCOMSMS_PASS',
                    'class'    => 'lg',
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'button',
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                    '&token='.Tools::getAdminTokenLite('AdminModules'),
                ],
                'back' => [
                    'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list'),
                ],
        ];

        // Load current value
        $helper->fields_value['DESCOMSMS_USER'] = Configuration::get('DESCOMSMS_USER');
        if (!empty(Configuration::get('DESCOMSMS_KEY'))) {
            $helper->fields_value['DESCOMSMS_PASS'] = $this->MyDecrypt(Configuration::get('DESCOMSMS_PASS'), Configuration::get('DESCOMSMS_KEY'));
        }

        return $helper->generateForm($fields_form);
    }

    /**************
    *** HOOKS
    **************/

    //Hook order status change
    public function hookActionOrderStatusPostUpdate($params)
    {
        //Get params to send
        $order = new Order($params['id_order']);
        $address = new Address($order->id_address_delivery);
        $customer = new Customer($order->id_customer);
        $country = new Country($address->id_country);

        if ((($order->current_state == 2 || $order->current_state == 12) && Configuration::get('DESCOMSMS_CHECK_ORDER_PAY') == 'on') || ($order->current_state == 4 && Configuration::get('DESCOMSMS_CHECK_ORDER_SEND') == 'on')) {
            $data = [
                'user'   => Configuration::get('DESCOMSMS_USER'),
                'pass'   => $this->MyDecrypt(Configuration::get('DESCOMSMS_PASS'), Configuration::get('DESCOMSMS_KEY')),
                'sender' => Configuration::get('DESCOMSMS_SENDER'),
            ];
            $data['mobile'] = $this->GetPhoneMobile($address, $country);

            // The message we will be sending
            if ($order->current_state == 2 || $order->current_state == 12) {
                $data['message'] = $this->GetSMSText(Configuration::get('DESCOMSMS_TEXT_ORDER_PAY'), $order->id, '', '');
            } elseif ($order->current_state == 4) {
                $data['message'] = $this->GetSMSText(Configuration::get('DESCOMSMS_TEXT_ORDER_SEND'), $order->id, '', '');
            }

            if (!empty($data['mobile'])) {
                $result = $this->SendSMS($data);
                //error_log(json_encode($result)); //TODO
            } else {
                error_log('There is no mobile phone number for this address:'.$order->id_address_delivery); //TODO
            }
        }
    }

    //Hook product stock change
    public function hookActionUpdateQuantity($params)
    {
        if ($this->CheckModuleInstaled('mailalerts') && Configuration::get('DESCOMSMS_CHECK_PRODUCT_STOCK') == 'on') {
            //Avoid entering 2 times in the hook when modifying product with combinations stock
            if (!$params['id_product_attribute']) {
                $sql = 'SELECT count(*) FROM '._DB_PREFIX_.'product_attribute where id_product = '.$params['id_product'];
                if ($this->db->getValue($sql)) {
                    return false;
                }
            }

            $sql = 'SELECT * FROM '._DB_PREFIX_.'mailalert_customer_oos WHERE id_product = '.$params['id_product'].' AND id_product_attribute = '.$params['id_product_attribute'];
            $results = $this->db->ExecuteS($sql);

            foreach ($results as $row) {
                $sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE id_customer = '.$row['id_customer'];
                $addresses = $this->db->ExecuteS($sql);
                $sended = false;
                foreach ($addresses as $address) {
                    if (!$sended || Configuration::get('DESCOMSMS_CHECK_PRODUCT_STOCK_ALL_ADDRESSES') == 'on') {
                        $address = new Address($address['id_address']);
                        $country = new Country($address->id_country);
                        if (!empty($this->GetPhoneMobile($address, $country))) {
                            $sql = 'SELECT name FROM '._DB_PREFIX_.'product_lang pl, '._DB_PREFIX_.'customer c WHERE pl.id_lang = c.id_lang AND pl.id_product = '.$params['id_product'].' AND c.id_customer = '.$row['id_customer'];
                            $name = $this->db->getValue($sql);

                            $data = [
                                'user'    => Configuration::get('DESCOMSMS_USER'),
                                'pass'    => $this->MyDecrypt(Configuration::get('DESCOMSMS_PASS'), Configuration::get('DESCOMSMS_KEY')),
                                'sender'  => Configuration::get('DESCOMSMS_SENDER'),
                                'message' => $this->GetSMSText(Configuration::get('DESCOMSMS_TEXT_PRODUCT_STOCK'), '', $name, $params['quantity']),
                            ];
                            $data['mobile'] = $this->GetPhoneMobile($address, $country);

                            $result = $this->SendSMS($data);
                            $sended = true;
                            //error_log(json_encode($result)); //TODO
                        }
                    }
                }
            }
        }
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $this->context->controller->addCSS(($this->_path).'css/menuTabIcon.css');
    }

    /**************
    *** FUNCTIONS
    **************/
    public function CreateTab($tabClass, $tabName, $idParent)
    {
        $tab = new Tab();
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = $tabName;
        }
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = $idParent;

        if (!$tab->save()) {
            return false;
        }

        return true;
    }

    public function DeleteTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();

            return true;
        }

        return false;
    }

    public function InitConfigValues()
    {
        if (empty(Configuration::get('DESCOMSMS_SENDER'))) {
            Configuration::updateValue('DESCOMSMS_SENDER', 'aviso');
        }
        if (empty(Configuration::get('DESCOMSMS_CHECK_ORDER_PAY'))) {
            Configuration::updateValue('DESCOMSMS_CHECK_ORDER_PAY', 'on');
        }
        if (empty(Configuration::get('DESCOMSMS_TEXT_ORDER_PAY'))) {
            Configuration::updateValue('DESCOMSMS_TEXT_ORDER_PAY', '[shop_name]: El pedido con id [order_id] ha sido pagado correctamente.');
        }
        if (empty(Configuration::get('DESCOMSMS_CHECK_ORDER_SEND'))) {
            Configuration::updateValue('DESCOMSMS_CHECK_ORDER_SEND', 'on');
        }
        if (empty(Configuration::get('DESCOMSMS_TEXT_ORDER_SEND'))) {
            Configuration::updateValue('DESCOMSMS_TEXT_ORDER_SEND', '[shop_name]: El pedido con id [order_id] ha sido enviado.');
        }
        if (empty(Configuration::get('DESCOMSMS_CHECK_PRODUCT_STOCK'))) {
            Configuration::updateValue('DESCOMSMS_CHECK_PRODUCT_STOCK', 'off');
        }
        if (empty(Configuration::get('DESCOMSMS_TEXT_PRODUCT_STOCK'))) {
            Configuration::updateValue('DESCOMSMS_TEXT_PRODUCT_STOCK', '[shop_name]: El producto [product_name] vuelve a tener stock ([product_stock] uds. disponibles).');
        }
        if (empty(Configuration::get('DESCOMSMS_TEXT_PRODUCT_STOCK_ALL_ADDRESSES'))) {
            Configuration::updateValue('DESCOMSMS_TEXT_PRODUCT_STOCK_ALL_ADDRESSES', 'off');
        }
    }

    public function GetModuleVersion($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public function MyEncrypt($data, $key)
    {
        $encryption_key = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);

        return base64_encode($encrypted.'::'.$iv);
    }

    public function MyDecrypt($data, $key)
    {
        $encryption_key = base64_decode($key);
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);

        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }

    //Put the module DescomSMS in the first position of the ActionUpdateQuantity hook
    public function SetHookModulePosition()
    {
        $idShops = [];

        $sql = 'SELECT distinct id_shop FROM '._DB_PREFIX_.'hook_module';
        if ($results = $this->db->ExecuteS($sql)) {
            foreach ($results as $row) {
                $idShops[] = $row['id_shop'];
            }
        }

        $sql = 'SELECT id_hook FROM '._DB_PREFIX_.'hook where name = "actionUpdateQuantity"';
        $idHook = $this->db->getValue($sql);

        foreach ($idShops as $idShop) {
            $sql = 'SELECT * FROM '._DB_PREFIX_.'hook_module WHERE id_shop = '.$idShop.' AND id_hook = '.$idHook.' ORDER BY position';
            if ($results = $this->db->ExecuteS($sql)) {
                $pos = 2;
                foreach ($results as $row) {
                    if ($row['id_module'] == $this->id) {
                        $query = 'UPDATE '._DB_PREFIX_.'hook_module SET position = '. 1 .' WHERE id_shop = '.$row['id_shop'].' AND id_hook = '.$row['id_hook'].' AND id_module = '.$row['id_module'];
                    } else {
                        $query = 'UPDATE '._DB_PREFIX_.'hook_module SET position = '.$pos.' WHERE id_shop = '.$row['id_shop'].' AND id_hook = '.$row['id_hook'].' AND id_module = '.$row['id_module'];
                        $pos++;
                    }
                    $this->db->Execute($query);
                }
            }
        }

        return true;
    }

    public function CheckModuleInstaled($nameModule)
    {
        $sql = 'SELECT id_module FROM '._DB_PREFIX_.'module where name = "'.$nameModule.'"';
        $idModule = $this->db->getValue($sql);

        return (bool) $idModule;
    }

    public function SaveSender($sender)
    {
        Configuration::updateValue('DESCOMSMS_SENDER', $sender);
    }

    public function SaveAlertOrderPay($checkPay, $textPay)
    {
        Configuration::updateValue('DESCOMSMS_CHECK_ORDER_PAY', $checkPay);
        Configuration::updateValue('DESCOMSMS_TEXT_ORDER_PAY', $textPay);
    }

    public function SaveAlertOrderSend($checkSend, $textSend)
    {
        Configuration::updateValue('DESCOMSMS_CHECK_ORDER_SEND', $checkSend);
        Configuration::updateValue('DESCOMSMS_TEXT_ORDER_SEND', $textSend);
    }

    public function SaveAlertProductStock($checkStock, $textStock)
    {
        Configuration::updateValue('DESCOMSMS_CHECK_PRODUCT_STOCK', $checkStock);
        Configuration::updateValue('DESCOMSMS_TEXT_PRODUCT_STOCK', $textStock);
    }

    public function GetPhoneMobile($address, $country)
    {
        $mobile = '';
        if (!empty($address->phone_mobile)) {
            $mobile = $address->phone_mobile;
        } else {
            $mobile = $address->phone;
        }
        if (substr($mobile, 0, 1) != '+') {
            $mobile = '+'.$country->call_prefix.$mobile;
        }
        //if(!preg_match($expresion, $mobile))
        //    return '';

        return $mobile;
    }

    public function GetSMSText($text, $orderId, $productName, $productStock)
    {
        $text = str_replace('[shop_name]', Configuration::get('PS_SHOP_NAME'), $text);
        $text = str_replace('[order_id]', $orderId, $text);
        $text = str_replace('[product_name]', $productName, $text);
        $text = str_replace('[product_stock]', $productStock, $text);

        return $text;
    }

    /**************
    *** SMS API
    **************/
    public function SendSMS($data)
    {
        try {
            $sms = new \Descom\Sms\Sms(new \Descom\Sms\Auth\AuthUser($data['user'], $data['pass']));
            $message = new \Descom\Sms\Message();
            $message->addTo($data['mobile'])->setSenderID($data['sender'])->setText($data['message']);
            $result = $sms->addMessage($message)
                    ->setDryrun(false)
                    ->send();

            error_log(json_encode($data));

            return $result;
        } catch (Exception $e) {
            error_log('DESCOMSMS module - Error sending message: '.$e->getMessage()); //TODO
        }
    }

    public function GetCreditsSMS($data)
    {
        try {
            $sms = new \Descom\Sms\Sms(new \Descom\Sms\Auth\AuthUser($data['user'], $data['pass']));
            $result = $sms->getBalance();

            return $result;
        } catch (Exception $e) {
            error_log('DESCOMSMS module - Error geting credits: '.$e->getMessage()); //TODO
        }
    }

    public function GetSendersSMS($data)
    {
        try {
            $sms = new \Descom\Sms\Sms(new \Descom\Sms\Auth\AuthUser($data['user'], $data['pass']));
            $result = $sms->getSenderID();

            return $result;
        } catch (Exception $e) {
            error_log('DESCOMSMS module - Error geting credits: '.$e->getMessage()); //TODO
        }
    }
}
