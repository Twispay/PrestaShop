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
