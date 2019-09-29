<?php
/**
 * Twispay Helpers
 *
 * Updates the statused of orders and subscriptions based
 *  on the status read from the server response.
 *
 * @author   Twistpay
 * @version  1.0.1
 */

/* Security class check */
if (! class_exists('Twispay_Status_Updater')) :
    /**
     * Class that implements methods to update the statuses
     * of orders and subscriptions based on the status received
     * from the server.
     */
    class Twispay_Status_Updater
    {
        /* Array containing the possible result statuses. */
          public static $RESULT_STATUSES = [ 'UNCERTAIN' => 'uncertain' /* No response from provider */
                                         , 'IN_PROGRESS' => 'in-progress' /* Authorized */
                                         , 'COMPLETE_OK' => 'complete-ok' /* Captured */
                                         , 'COMPLETE_FAIL' => 'complete-failed' /* Not authorized */
                                         , 'CANCEL_OK' => 'cancel-ok' /* Capture reversal */
                                         , 'REFUND_OK' => 'refund-ok' /* Settlement reversal */
                                         , 'VOID_OK' => 'void-ok' /* Authorization reversal */
                                         , 'CHARGE_BACK' => 'charge-back' /* Charge-back received */
                                         , 'THREE_D_PENDING' => '3d-pending' /* Waiting for 3d authentication */
                                         , 'EXPIRING' => 'expiring' /* The recurring order has expired */
                                         , 'REFUND_REQUESTED' => 'refund-requested' /* The recurring order has expired */
                                         ];
        /**
         * Update the status of an order according to the received server status.
         *
         * @param object decrypted: Decrypted order message.
         * @param object translator: Language object
         * @param string controller: Controller instance use for accessing runtime values like configuration, active language, etc.
         *
         * @return void
         */
        public static function updateStatus_backUrl($decrypted, $translator, $controller)
        {
          /** Represents the cart id */
          $id_cart = $decrypted['externalOrderId'];

            $completed_ok = false;
            switch ($decrypted['status']) {
                case Twispay_Status_Updater::$RESULT_STATUSES['COMPLETE_FAIL']:
                    /** Mark order as Failed. */
                    $id_status = Configuration::get('PS_OS_ERROR');
                    $order_message = $translator->trans('Twispay payment failed');
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status failed for cart ID: ').$id_cart);
                break;

                case Twispay_Status_Updater::$RESULT_STATUSES['THREE_D_PENDING']:
                    /** Mark order as Pending. */
                    $id_status = Configuration::get('PS_OS_CHEQUE');
                    $order_message = $translator->trans('Twispay payment is on hold');
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status on-hold for cart ID: ').$id_cart);
                break;

                case Twispay_Status_Updater::$RESULT_STATUSES['IN_PROGRESS']:
                case Twispay_Status_Updater::$RESULT_STATUSES['COMPLETE_OK']:
                    /** Mark order as Processing. */
                    $id_status = Configuration::get('PS_OS_PAYMENT');
                    $order_message =  $translator->trans('Paid Twispay');
                    $completed_ok = true;
                    $amount = (float)$decrypted['amount'];
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status complete-ok for cart ID: ').$id_cart);
                break;

                default:
                    Twispay_Logger::log($translator->trans('[RESPONSE-ERROR]: Wrong status: ').$decrypted['status']);
                    return $controller->showNotice();
                break;
            }

               /** Check if cart is valid */
               $cart = new Cart($id_cart);
               if (!Validate::isLoadedObject($cart)) {
                   Twispay_Logger::log(sprintf($translator->trans('[RESPONSE-ERROR]: Cart #%s could not be loaded'), $id_cart));
                   return $controller->showNotice();
               }

               /** Check if customer is valid */
               $id_customer = (int)$cart->id_customer;
               $decrypted['customerId'] = $id_customer;

               $customer = new Customer($id_customer);
               if (!Validate::isLoadedObject($customer)) {
                   Twispay_Logger::log(sprintf($translator->trans('[RESPONSE-ERROR]: Customer #%s could not be loaded.'), $id_customer));
                   return $controller->showNotice();
               }

               $id_currency = (int)Currency::getIdByIsoCode($decrypted['currency']);
               if (!$id_currency) {
                   Twispay_Logger::log($translator->trans($translator->trans('[RESPONSE-ERROR]: Wrong Currency: '). $decrypted['currency']));
                   return $controller->showNotice();
               }

              if($id_status){
                $order_id = Order::getOrderByCartId((int)$cart->id);

                if($order_id){
                  $order = new Order($order_id);
                  Twispay_Logger::log($translator->trans('[RESPONSE]: Order updated.'));
                  if ($amount!=0 && !$order->addOrderPayment($amount, null, null)) {
                      Twispay_Logger::log($translator->trans('[RESPONSE-ERROR]: Order payment registration failed'));
                      return $controller->showNotice();
                  }
                  $order->setCurrentState($id_status);
                }else{
                  if ($controller->module->validateOrder(
                        $id_cart,
                        $id_status,
                        $amount?$amount:0,
                        $controller->module->displayName,
                        $order_message,
                        null,
                        $id_currency,
                        false,
                        $customer->secure_key
                  )){
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Order created.'));
                  }else{
                    Twispay_Logger::log($translator->trans('[RESPONSE-ERROR]: Could not validate order'));
                  }
                }
              }

              if($completed_ok){
                Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module=' .$module_id.'&id_order='.$order_id.'&key='.$secure_key);
                // die("OK");
              }else{
                return $controller->showNotice();
              }
        }

        /**
         * Update the status of an order according to the received server status.
         *
         * @param object decrypted: Decrypted order message.
         * @param object translator: Language object
         * @param string controller: Controller instance use for accessing runtime values like configuration, active language, etc.
         *
         * @return void
         */

        public static function updateStatus_ipn($decrypted, $translator, $controller)
        {
          /** Represents the cart id */
          $id_cart = $decrypted['externalOrderId'];
            $completed_ok = false;
            switch ($decrypted['status']) {
                case Twispay_Status_Updater::$RESULT_STATUSES['EXPIRING']:
                case Twispay_Status_Updater::$RESULT_STATUSES['CANCEL_OK']:
                    /** Mark order as Canceled. */
                    $id_status = Configuration::get('PS_OS_CANCELED');
                    $order_message = $translator->trans('Twispay payment was canceled');
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status canceled for cart ID: ').$id_cart);
                break;
                case Twispay_Status_Updater::$RESULT_STATUSES['VOID_OK']:
                    /** Mark order as . */
                    // $id_status = Configuration::get('PS_OS_ERROR');
                    $order_message = $translator->trans('Twispay payment was voided');
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status voided for cart ID: ').$id_cart);
                break;

                case Twispay_Status_Updater::$RESULT_STATUSES['CHARGE_BACK']:
                    /** Mark order as . */
                    // $id_status = Configuration::get('PS_OS_ERROR');
                    $order_message = $translator->trans('Twispay payment was charged_back');
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status charged back for cart ID: ').$id_cart);
                break;

                case Twispay_Status_Updater::$RESULT_STATUSES['REFUND_OK']:
                    /** Mark order as Refunded. */
                    $id_status = Configuration::get('PS_OS_REFUND');
                    $order_message = $translator->trans('Twispay payment was refunded');
                    $amount = (float)$decrypted['amount']*-1;
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status refunded for cart ID: ').$id_cart);
                break;

                case Twispay_Status_Updater::$RESULT_STATUSES['COMPLETE_FAIL']:
                    /** Mark order as Failed. */
                    $id_status = Configuration::get('PS_OS_ERROR');
                    $order_message = $translator->trans('Twispay payment failed');
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status failed for cart ID: ').$id_cart);
                break;

                case Twispay_Status_Updater::$RESULT_STATUSES['THREE_D_PENDING']:
                    /** Mark order as Pending. */
                    // $id_status = Configuration::get('PS_OS_CHEQUE');
                    $order_message = $translator->trans('Twispay payment is panding');
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status panding for cart ID: ').$id_cart);
                break;

                case Twispay_Status_Updater::$RESULT_STATUSES['IN_PROGRESS']:
                case Twispay_Status_Updater::$RESULT_STATUSES['COMPLETE_OK']:
                    /** Mark order as Processing. */
                    $id_status = Configuration::get('PS_OS_PAYMENT');
                    $order_message =  $translator->trans('Paid Twispay');
                    $completed_ok = true;
                    $amount = (float)$decrypted['amount'];
                    Twispay_Logger::log($translator->trans('[RESPONSE]: Status complete-ok for cart ID: ').$id_cart);
                break;

                default:
                    Twispay_Logger::log($translator->trans('[RESPONSE-ERROR]: Wrong status: ').$decrypted['status']);
                    die();
                break;
            }

            /** Check if cart is valid */
            $cart = new Cart($id_cart);
            if (!Validate::isLoadedObject($cart)) {
                Twispay_Logger::log(sprintf($translator->trans('[RESPONSE-ERROR]: Cart #%s could not be loaded'), $id_cart));
                die();
            }

            /** Check if customer is valid */
            $id_customer = (int)$cart->id_customer;
            $decrypted['customerId'] = $id_customer;

            $customer = new Customer($id_customer);
            if (!Validate::isLoadedObject($customer)) {
                Twispay_Logger::log(sprintf($translator->trans('[RESPONSE-ERROR]: Customer #%s could not be loaded.'), $id_customer));
                die();
            }

            $id_currency = (int)Currency::getIdByIsoCode($decrypted['currency']);
            if (!$id_currency) {
                Twispay_Logger::log($translator->trans($translator->trans('[RESPONSE-ERROR]: Wrong Currency: '). $decrypted['currency']));
                die();
            }

           if($id_status){
             $order_id = Order::getOrderByCartId((int)$cart->id);
             if($order_id){
               $order = new Order($order_id);
               Twispay_Logger::log($translator->trans('[RESPONSE]: Order updated.'));
               if ($amount!=0 && !$order->addOrderPayment($amount, null, null)) {
                   Twispay_Logger::log($translator->trans('[RESPONSE-ERROR]: Order payment registration failed'));
               }
               $order->setCurrentState($id_status);
             }else{
               if ($controller->module->validateOrder(
                     (int)$id_cart,
                     $id_status,
                     $amount?$amount:0,
                     $controller->module->displayName,
                     $order_message,
                     null,
                     $id_currency,
                     false,
                     $customer->secure_key
               )){
                 Twispay_Logger::log($translator->trans('[RESPONSE]: Order created.'));
               }else{
                 Twispay_Logger::log($translator->trans('[RESPONSE-ERROR]: Could not validate order'));
               }
             }
             if($completed_ok){
               die('OK');
             }else{
               die();
             }
           }
        }
    }
endif; /* End if class_exists. */
