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

$(function() {
    /* Triggers for hiding and showing LIVE/STAGING INPUTS */
    $(document).ready(TwispayCheckLiveOrStaging);
    $(document).on('change', 'input[name="TWISPAY_LIVE_MODE"]', TwispayCheckLiveOrStaging);
    
    
});

/* Function to hide or show LIVE/STAGING inputs on module configuration page */
function TwispayCheckLiveOrStaging() {
    if (!$(document).find('input[name="TWISPAY_LIVE_MODE"]:checked').length) {
        return;
    }
    var isLive = parseInt($(document).find('input[name="TWISPAY_LIVE_MODE"]:checked').val());
    if (isLive) {
        $('#TWISPAY_SITEID_STAGING, #TWISPAY_PRIVATEKEY_STAGING').closest('.form-group').slideUp();
        $('#TWISPAY_SITEID_LIVE, #TWISPAY_PRIVATEKEY_LIVE').closest('.form-group').slideDown();
    }
    else {
        $('#TWISPAY_SITEID_STAGING, #TWISPAY_PRIVATEKEY_STAGING').closest('.form-group').slideDown();
        $('#TWISPAY_SITEID_LIVE, #TWISPAY_PRIVATEKEY_LIVE').closest('.form-group').slideUp();
    }
}