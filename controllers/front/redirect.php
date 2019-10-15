<?php
/**
 * @author   Twistpay
 * @version  1.0.1
 */

class TwispayRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        $this->context->controller->display_column_left = false;
        $this->context->controller->display_column_right = false;
        /**
         * Oops, an error occured.
         */
        if (Tools::getValue('action') == 'error') {
            return $this->displayError('An error occurred while trying to redirect the customer');
        } else {
            $this->context->smarty->assign(
                $this->module->getPaymentVars()
            );
            $this->context->controller->addJs($this->module->getPath().'/views/js/redirect.js');
            $this->context->smarty->assign('module_path', $this->module->getPath());

            return $this->setTemplate('redirect.tpl');
        }
    }
}
