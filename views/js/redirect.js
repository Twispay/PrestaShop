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

twispay_payment_interval = setInterval(function() {
    if (!document.getElementById('twispay_payment_form')) {
        return;
    }
    clearInterval(twispay_payment_interval);
    document.getElementById('twispay_payment_form').submit();
}, 100);
