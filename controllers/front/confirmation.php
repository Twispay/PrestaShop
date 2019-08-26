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

class TwispayConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return false;
        }

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);

        /**
         * If the order has been validated we try to retrieve it
         */
        $order_id = Order::getOrderByCartId((int)$cart->id);
        
        $module_id = $this->module->id;

        if ($order_id && ($secure_key == $customer->secure_key)) {
            /**
             * The order has been placed so we redirect the customer on the confirmation page.
             */

            Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module=' .
                $module_id.'&id_order='.$order_id.'&key='.$secure_key.'&validated_by_ipn=1');
        } else {
            if ($result = Tools::getValue('result')) {
                $decrypted = $this->module->twispayDecrypt($result);
                $orderValidation = $this->module->checkValidation($decrypted, false);
                if ($orderValidation) {
                    Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.
                        $module_id.'&id_order='.$order_id.'&key='.$secure_key);
                    die();
                }
            }
            if ($result = Tools::getValue('opensslResult')) {
                $decrypted = $this->module->twispayDecrypt($result);
                $orderValidation = $this->module->checkValidation($decrypted, false);
                if ($orderValidation) {
                    Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.
                        $module_id.'&id_order='.$order_id.'&key='.$secure_key);
                    die();
                }
            }
            if (Tools::version_compare(_PS_VERSION_, '1.7', '>')) {
                return $this->setTemplate('module:twispay/views/templates/front/error_ps17.tpl');
            } else {
                return $this->context->controller->setTemplate('error.tpl');
            }
        }
    }
}
