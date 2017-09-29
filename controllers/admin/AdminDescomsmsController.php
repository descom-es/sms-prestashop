<?php

class AdminDescomsmsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit_sender')) {
            Configuration::updateValue('DESCOMSMS_SENDER', Tools::getValue('selectSender'));
        }

        if (Tools::isSubmit('submit_pay')) {
            Configuration::updateValue('DESCOMSMS_CHECK_PRODUCT_STOCK', Tools::getValue('checkPay'));
            Configuration::updateValue('DESCOMSMS_TEXT_ORDER_PAY', Tools::getValue('textareaPay'));
        }

        if (Tools::isSubmit('submit_send')) {
            Configuration::updateValue('DESCOMSMS_CHECK_PRODUCT_STOCK', Tools::getValue('checkSend'));
            Configuration::updateValue('DESCOMSMS_TEXT_ORDER_SEND', Tools::getValue('textareaSend'));
        }

        if (Tools::isSubmit('submit_stock')) {
            Configuration::updateValue('DESCOMSMS_CHECK_PRODUCT_STOCK', Tools::getValue('checkStock'));
            Configuration::updateValue('DESCOMSMS_TEXT_PRODUCT_STOCK', Tools::getValue('textareaStock'));
        }
    }

    public function initContent()
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

        $data = [
          'user'                => strval(Configuration::get('DESCOMSMS_USER')),
          'pass'                => $this->module->my_decrypt(Configuration::get('DESCOMSMS_PASS'), Configuration::get('DESCOMSMS_KEY')),
          'sender'              => strval(Configuration::get('DESCOMSMS_SENDER')),
          'check_order_pay'     => strval(Configuration::get('DESCOMSMS_CHECK_ORDER_PAY')),
          'text_order_pay'      => strval(Configuration::get('DESCOMSMS_TEXT_ORDER_PAY')),
          'check_order_send'    => strval(Configuration::get('DESCOMSMS_CHECK_ORDER_SEND')),
          'text_order_send'     => strval(Configuration::get('DESCOMSMS_TEXT_ORDER_SEND')),
          'check_product_stock' => strval(Configuration::get('DESCOMSMS_CHECK_PRODUCT_STOCK')),
          'text_product_stock'  => strval(Configuration::get('DESCOMSMS_TEXT_PRODUCT_STOCK')),
        ];
        $data['credits'] = $this->module->GetCreditsSMS($data);
        $data['senders'] = $this->module->GetSendersSMS($data);

        $this->context->smarty->assign($data);

        parent::initContent();
    }

    public function renderList()
    {
        return $this->module->display(_PS_MODULE_DIR_.'descomsms', 'views/templates/admin/descomsms_home.tpl');
    }
}
