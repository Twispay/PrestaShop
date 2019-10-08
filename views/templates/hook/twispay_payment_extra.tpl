{*
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
*}

<div class="twispay_payment_logos">
    <img src="{$logos_folder|escape:'quotes'}visa.png" height="17px"/>
    <img src="{$logos_folder|escape:'quotes'}mastercard.png" height="17px"/>
    <div class="twispay-separator"></div>
    <img src="{$logos_folder|escape:'quotes'}twispay.png" height="23px"/>
    <div class="twispay_secure_div">
        <img src="{$logos_folder|escape:'quotes'}secure.png" height="15px"/> <span>{l s='Secure' mod='twispay'}</span>
    </div>
    <p>{l s='You will be redirected to a secure payment page to complete your payment' mod='twispay'}</p>
</div>