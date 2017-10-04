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
        $data = [
          'user'                => strval(Configuration::get('DESCOMSMS_USER')),
          'pass'                => $this->module->MyDecrypt(Configuration::get('DESCOMSMS_PASS'), Configuration::get('DESCOMSMS_KEY')),
          'sender'              => strval(Configuration::get('DESCOMSMS_SENDER')),
          'check_order_pay'     => strval(Configuration::get('DESCOMSMS_CHECK_ORDER_PAY')),
          'text_order_pay'      => strval(Configuration::get('DESCOMSMS_TEXT_ORDER_PAY')),
          'check_order_send'    => strval(Configuration::get('DESCOMSMS_CHECK_ORDER_SEND')),
          'text_order_send'     => strval(Configuration::get('DESCOMSMS_TEXT_ORDER_SEND')),
          'check_product_stock' => strval(Configuration::get('DESCOMSMS_CHECK_PRODUCT_STOCK')),
          'text_product_stock'  => strval(Configuration::get('DESCOMSMS_TEXT_PRODUCT_STOCK')),
          'version'             => strval($this->module->version),
          'need_update'         => false,
        ];
        $data['credits'] = $this->module->GetCreditsSMS($data);
        $data['senders'] = $this->module->GetSendersSMS($data);

        $versionLatest = json_decode($this->module->GetModuleVersion($this->module->versionURL));
        if(!empty($versionLatest)){
            $data['version_latest'] = $versionLatest->version;
            $data['version_latest_url'] = $versionLatest->downloadURL;
            if((int)str_replace('.','',$data['version']) < (int)str_replace('.','',$data['version_latest']))
                $data['need_update'] = true;
        }
        else{
            $data['version_latest'] = $this->l('Unable to get latest version.');
            $data['version_latest_url'] = '';
        }


        $this->context->smarty->assign($data);

        parent::initContent();
    }

    public function renderList()
    {
        return $this->module->display(_PS_MODULE_DIR_.'descomsms', 'views/templates/admin/descomsms_home.tpl');
    }
}
