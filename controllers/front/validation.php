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

class TwispayValidationModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        if (Tools::getValue('opensslResult')) {
            $opensslResult = Tools::getValue('opensslResult');
            $decrypted = $this->module->twispayDecrypt($opensslResult);
            if ($decrypted) {
                $checkValidation = $this->module->checkValidation($decrypted, true);
                if ($checkValidation) {
                    die("OK");
                } else {
                    die("ERROR");
                }
            } else {
                $this->module->log('[ERROR] Decryption did not worked.');
                $this->module->log('opensslResult: '.$opensslResult);
                $this->module->log('decrypted string: '.$decrypted);
                $this->module->log();
                die("ERROR");
            }
        }
        die("NO DATA SENT");
    }
}
