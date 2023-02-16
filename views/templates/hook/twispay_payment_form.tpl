{**
* @author   Twispay
* @version  1.4.0
*}

{if $action && $inputs}
  <form accept-charset="UTF-8" id="twispay_payment_form" action="{$action|escape:'quotes'}" method="POST">
    <input type="hidden" name="jsonRequest" value="{$inputs['jsonRequest']|escape:'htmlall':'UTF-8'}" />
    <input type="hidden" name="checksum" value="{$inputs['checksum']|escape:'htmlall':'UTF-8'}" />
    {if $isIframe}
      <button type="button" class="btn-primary" id="payButton">						
        {l s='Pay now' mod='twispay'}
      </button>
    {/if}
  </form>

  {if $isIframe}
    <script type="text/javascript">
      var twispayButtonsSelectors = [
		    '#payButton'
	    ];
    </script>
    <script type="text/javascript" src="{$iframeScriptUrl}"></script>
  {/if}
{/if}

