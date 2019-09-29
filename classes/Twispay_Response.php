<?php
/**
 * Twispay Helpers
 *
 * Decodes and validates notifications sent by the Twispay server.
 *
 * @author   Twistpay
 * @version  1.0.1
 */


/* Security class check */
if (! class_exists('Twispay_Response')) :
    /**
     * Class that implements methods to decrypt
     * Twispay server responses.
     */
    class Twispay_Response
    {
        /**
         * Decrypt the response from Twispay server.
         *
         * @param string $tw_encryptedMessage - The encripted server message.
         * @param string $tw_secretKey        - The secret key (from Twispay).
         *
         * @return Array([key => value,]) - If everything is ok array containing the decrypted data.
         *         bool(FALSE)            - If decription fails.
         */
        public static function Twispay_decrypt_message($tw_encryptedMessage, $tw_secretKey)
        {
            $encrypted = ( string )$tw_encryptedMessage;

            if (!strlen($encrypted) || (FALSE == strpos($encrypted, ','))) {
                return FALSE;
            }

            /* Get the IV and the encrypted data */
            $encryptedParts = explode(/*delimiter*/',', $encrypted, /*limit*/2);
            $iv = base64_decode($encryptedParts[0]);
            if (FALSE === $iv) {
                return FALSE;
            }

            $encryptedData = base64_decode($encryptedParts[1]);
            if (FALSE === $encryptedData) {
                return FALSE;
            }

            /* Decrypt the encrypted data */
            $decryptedResponse = openssl_decrypt($encryptedData, /*method*/'aes-256-cbc', $tw_secretKey, /*options*/OPENSSL_RAW_DATA, $iv);

            if (FALSE === $decryptedResponse) {
                return FALSE;
            }

            /* JSON decode the decrypted data. */
            $decryptedResponse = json_decode($decryptedResponse, /*assoc*/TRUE, /*depth*/4);

            /* Normalize values */
            $decryptedResponse['status'] = (empty($decryptedResponse['status'])) ? ($decryptedResponse['transactionStatus']) : ($decryptedResponse['status']);
            $decryptedResponse['externalOrderId'] = explode('_', $decryptedResponse['externalOrderId'])[0];
            $decryptedResponse['cardId'] = (!empty($decryptedResponse['cardId'])) ? ($decryptedResponse['cardId']) : (0);

            return $decryptedResponse;
        }

        /**
         * Function that validates a decripted response.
         *
         * @param tw_response The server decripted and JSON decoded response
         * @param translator Language object
         *
         * @return bool(FALSE)     - If any error occurs
         *         bool(TRUE)      - If the validation is successful
         */

        public static function twispay_checkValidation($tw_response, $translator)
        {
            $tw_errors = array();

            if (!$tw_response) {
                return FALSE;
            }

            if (empty($tw_response['status']) && empty($tw_response['transactionStatus'])) {
                $tw_errors[] = $translator->trans('[RESPONSE-ERROR]: Empty status.');
            }

            if (empty($tw_response['identifier'])) {
                $tw_errors[] = $translator->trans('[RESPONSE-ERROR]: Empty identifier.');
            }

            if (empty($tw_response['externalOrderId'])) {
                $tw_errors[] = $translator->trans('[RESPONSE-ERROR]: Empty externalOrderId.');
            }

            if (empty($tw_response['transactionId'])) {
                $tw_errors[] = $translator->trans('[RESPONSE-ERROR]: Empty transactionId.');
            }

            $id_cart = (!empty($tw_response['externalOrderId'])) ? explode('_', $tw_response['externalOrderId'])[0] : 0;
            $cart = new Cart($id_cart);
            $cartFound = false;
            if (Validate::isLoadedObject($cart)) {
                $cartFound = true;
            }

            if (empty($tw_response['amount'])) {
                if ($cartFound) {
                    $tw_response['amount'] = (float)number_format((float)$cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
                } else {
                    $tw_errors[] = $translator->trans('[RESPONSE-ERROR]: Empty amount');
                }
            }

            if (empty($tw_response['currency'])) {
                if ($cartFound) {
                    $currency = new Currency($cart->id_currency);
                    if (Validate::isLoadedObject($currency)) {
                        $tw_response['currency'] = $currency->iso_code;
                    } else {
                        $tw_errors[] = $translator->trans('[RESPONSE-ERROR]: Empty currency');
                    }
                } else {
                    $tw_errors[] = $translator->trans('[RESPONSE-ERROR]: Empty currency');
                }
            }

            if (sizeof($tw_errors)) {
                foreach ($tw_errors as $err) {
                    Twispay_Logger::log($err);
                }
                return FALSE;
            } else {
                $data = [ 'status'          => $tw_response['status']
                        , 'id_cart'         => (int)$tw_response['externalOrderId']
                        , 'identifier'      => $tw_response['identifier']
                        , 'customerId'      => (int)$tw_response['customerId']
                        , 'orderId'         => (int)$tw_response['orderId']
                        , 'cardId'          => (int)$tw_response['cardId']
                        , 'transactionId'   => (int)$tw_response['transactionId']
                        , 'transactionKind' => $tw_response['transactionKind']
                        , 'amount'          => (float)$tw_response['amount']
                        , 'currency'        => $tw_response['currency']
                        , 'timestamp'       => $tw_response['timestamp']];

                Twispay_Transactions::insertTransaction($data);
                Twispay_Logger::log($translator->trans('[RESPONSE]: Data: %s').Tools::jsonEncode($data));

                if (!in_array($data['status'], Twispay_Status_Updater::$RESULT_STATUSES)) {
                    Twispay_Logger::log($translator->trans('[RESPONSE-ERROR]: Wrong status: ').$data['status']);
                    return FALSE;
                }
                Twispay_Logger::log($translator->trans('[RESPONSE]: Validating completed for cart ID: ').$data['id_cart']);
                return TRUE;
            }
        }
    }
endif; /* End if class_exists. */
