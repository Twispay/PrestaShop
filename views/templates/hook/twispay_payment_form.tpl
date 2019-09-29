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

{if $action && $inputs}
  <form accept-charset="UTF-8" id="twispay_payment_form" action="{$action|escape:'quotes'}" method="POST">
    <input type="hidden" name="jsonRequest" value="{$inputs['jsonRequest']|escape:'htmlall':'UTF-8'}" />
    <input type="hidden" name="checksum" value="{$inputs['checksum']|escape:'htmlall':'UTF-8'}" />
  </form>
{/if}
