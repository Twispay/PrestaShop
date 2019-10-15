<?php
/**
 * @author   Twistpay
 * @version  1.0.1
 */

class TwispayValidationModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        /* Check if the POST is corrupted: Doesn't contain the 'opensslResult' and the 'result' fields. */
        if (false == Tools::getValue('opensslResult') && false == Tools::getValue('result')) {
            Twispay_Logger::log($this->l('[RESPONSE-ERROR]: Received empty response.'));
            die("NO DATA SENT");
        }

        /** Check if the api key is defined */
        $keys = $this->module->getKeysInfo();
        if (!$keys) {
            Twispay_Logger::log($this->l('[RESPONSE-ERROR]: Private key is not valid.'));
            die();
        }
        $apiKey = $keys['privateKey'];

        /* Extract the server response and decript it. */
        $decrypted = Twispay_Response::twispay_decrypt_message(/*tw_encryptedResponse*/Tools::getValue('opensslResult') != false ? Tools::getValue('opensslResult') : Tools::getValue('result'), $apiKey);

        /* Check if decryption failed.  */
        if (false === $decrypted) {
            Twispay_Logger::log($this->l('[RESPONSE-ERROR]: Decryption failed.'));
            die();
        } else {
            Twispay_Logger::log($this->l('[RESPONSE]: Decrypted string: ').Tools::jsonEncode($decrypted));
        }

        /** Check if order already exist */
        if (Twispay_Transactions::checkTransaction($decrypted['transactionId'])) {
            Twispay_Logger::log($this->l('[RESPONSE-ERROR]: Order already validated, transaction id '). $decrypted['transactionId']);
            die();
        }

        /* Validate the decripted response. */
        $orderValidation = Twispay_Response::twispay_checkValidation($decrypted, $this->translator);

        /* Check if server response validation failed.  */
        if (true !== $orderValidation) {
            Twispay_Logger::log($this->l('[RESPONSE-ERROR]: Validation failed.'));
            die();
        }

        /** Update the transaction status. */
        Twispay_Status_Updater::updateStatus_ipn($decrypted, $this->translator, $this);
    }
}
