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
    {foreach from=$inputs item=value key=name}
      {if !is_array($value)}
        <input type="hidden" name="{$name|escape:'htmlall':'UTF-8'}" value="{$value|escape:'htmlall':'UTF-8'}" />
      {else}
        {foreach from=$value item=subvalue key=subname}
          <input type="hidden" name="{$name|escape:'htmlall':'UTF-8'}[{$subname|escape:'htmlall':'UTF-8'}]" value="{$subvalue|escape:'htmlall':'UTF-8'}" />
        {/foreach}
      {/if}
    {/foreach}
  </form>
{/if}